<?php

namespace App\Console\Commands;

use App\Enums\LinkType;
use App\Models\BusinessWorker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:deactivate-expired-links')]
#[Description('Deactivate temporary worker links that have expired')]
class DeactivateExpiredLinks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = BusinessWorker::where('is_active', true)
            ->where('link_type', LinkType::Temporary)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expired as $link) {
            $link->unlink();
            $count++;
        }

        $this->info("Deactivated {$count} expired link(s).");

        return self::SUCCESS;
    }
}
