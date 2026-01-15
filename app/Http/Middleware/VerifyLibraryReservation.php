<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLibraryReservation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
          // Get distinct library_item_ids that have borrowings
        $libraryItemIds = Borrowing::distinct()->pluck('library_item_id');

        $expiredCount = 0;
        foreach ($libraryItemIds as $itemId) {
            // Get the latest borrowing for this item
            $latestBorrowing = Borrowing::where('library_item_id', $itemId)
                ->latest('id')
                ->first();

            if ($latestBorrowing && $latestBorrowing->status === 'reserved' && $latestBorrowing->date <= now()->subHours(24)) {
                Borrowing::create([
                    'user_id' => $latestBorrowing->user_id,
                    'library_item_id' => $itemId,
                    'date' => now()->toDateString(),
                    'status' => 'returned',
                ]);
                $expiredCount++;
            }
        }
        return $next($request);
    }
}
