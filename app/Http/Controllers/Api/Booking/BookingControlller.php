<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Auditorium;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class BookingControlller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addMonths(2);
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $bookedItems = Booking::with(['user:id,name', 'auditorium:id,title']) // eager load user relation
            ->whereBetween('booking_date', [$today, $endDate])
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->map(function ($booking) {
                return [
                    'auditorium' => $booking->auditorium,
                    'user' => $booking->user,         // or $booking->user->name if you want just the name
                    'id' => $booking->id,
                    'title' => $booking->title,
                    'status' => $booking->status,
                    'booking_date' => $booking->booking_date->format('Y-m-d'), // ensure consistent format
                ];
            });

        // 2. Generate all dates in the next 2 months
        $period = CarbonPeriod::create($today, $endDate);
        $allDates = collect($period)->map->format('Y-m-d')->toArray();
        //  filtering booked dates because each date can have one auditorium booking only at a date by a user
        $bookedDates = Booking::whereBetween('booking_date', [$today, $endDate])
            ->where('status', 'approved')
            ->pluck('booking_date')
            ->map->format('Y-m-d')
            ->unique()
            ->toArray();
        // 3. Get only the dates that are already booked
        // $bookedDates = $bookedItems->where('status', 'approved')->pluck('booking_date')->toArray();

        // 4. Available dates = all dates minus booked dates
        $availableDates = array_diff($allDates, $bookedDates);
        $availableDates = array_values($availableDates); // re-index

        // 5. Final response: booked items + available dates list
        return response()->json([
            'booked' => $bookedItems,
            'available_dates' => $availableDates,
        ]);
    }

    public function getDatesAuditoriuWise(Auditorium $auditoriumId)
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addMonths(2);
        $getAllBookings = Booking::where('auditorium_id', $auditoriumId)->get();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookingRequest $request)
    {
        $validated = $request->validated();
        $isAdmin = $request->user()->role === 'admin';
        if ($isAdmin) {
            $user = User::where('cnic', $validated['cnic_number'])->where('role', 'member')->first();
            if (! $user) {
                return response()->json([
                    'message' => 'User must be a member',
                ], 404);
            }
            $validated['user_id'] = $user->id;
        } else {
            $validated['user_id'] = $request->user()->id;
        }
        $validated['booked_by'] = $request->user()->id;
        $latestBooking = Booking::create($validated);

        return response()->json([
            'message' => 'Event Booked Successfully.',
            'data' => [
                'latestBooking' => $latestBooking,
            ],
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingRequest $request, $id)
    {
        $validated = $request->validated();
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Event Booking not found.',
            ], 404);
        }
        $booking->update($validated);

        return response()->json([
            'message' => 'Booking Event updated successfully.',
            'data' => $booking->refresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Event Booking not found.',
            ], 404);
        }
        $booking->delete();

        return response()->json([
            'message' => 'Requested Booking Event removed successfully.',
            'data' => $booking->refresh(),
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Event Booking not found.',
            ], 404);
        }
        $validated = $request->validate([
            'status' => 'required|in:requested,approved',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $booking->refresh(),
        ], 200);
    }
}
