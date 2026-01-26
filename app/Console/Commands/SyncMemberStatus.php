<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;

class SyncMemberStatus extends Command
{
    protected $signature = 'members:sync-status';
    protected $description = 'Recalculate expiry dates for all members based on transactions';

    public function handle()
    {
        $this->info('Syncing member statuses...');

        $members = Member::all();
        $bar = $this->output->createProgressBar(count($members));

        foreach ($members as $member) {
            $latestTrx = Transaction::where('member_id', $member->id)
                ->where('transaction_type', 'membership')
                ->orderBy('membership_end_date', 'desc')
                ->first();

            if ($latestTrx && $latestTrx->membership_end_date) {
                $member->current_expiry_date = $latestTrx->membership_end_date;
                $member->status = Carbon::parse($latestTrx->membership_end_date)->isFuture() ? 'active' : 'inactive';
            } else {
                $member->current_expiry_date = null;
                $member->status = 'inactive';
            }
            $member->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Sync complete!');
    }
}
