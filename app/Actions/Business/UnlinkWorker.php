<?php

namespace App\Actions\Business;

use App\Models\Business;
use App\Models\BusinessWorker;
use Illuminate\Validation\ValidationException;

class UnlinkWorker
{
    /**
     * Unlink a worker from a business.
     */
    public function execute(Business $business, int $workerId): BusinessWorker
    {
        $link = BusinessWorker::where('business_id', $business->id)
            ->where('worker_id', $workerId)
            ->where('is_active', true)
            ->first();

        if (! $link) {
            throw ValidationException::withMessages([
                'worker' => __('This worker is not actively linked to your business.'),
            ]);
        }

        $link->unlink();

        return $link->fresh();
    }
}
