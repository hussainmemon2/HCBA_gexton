<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use Illuminate\Console\Command;

class ExpireReservedBorrowings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-reserved-borrowings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire reserved borrowings that have passed 24 hours and set them to returned';

    /**
     * Execute the console command.
     */
    public function handle()
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

        $this->info("Expired {$expiredCount} reserved borrowings by creating returned records.");
    }
}
