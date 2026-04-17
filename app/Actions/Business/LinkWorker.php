<?php

namespace App\Actions\Business;

use App\Enums\LinkType;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LinkWorker
{
    /**
     * Link a worker to a business by their global number.
     *
     * @param  array{global_number: string, link_type?: string, expires_at?: string|null}  $data
     */
    public function execute(Business $business, array $data): BusinessWorker
    {
        $worker = User::where('global_number', $data['global_number'])->first();

        if (! $worker) {
            throw ValidationException::withMessages([
                'global_number' => __('No worker found with this number.'),
            ]);
        }

        if ($worker->role !== UserRole::Worker) {
            throw ValidationException::withMessages([
                'global_number' => __('This user is not a worker.'),
            ]);
        }

        $existingActive = BusinessWorker::where('business_id', $business->id)
            ->where('worker_id', $worker->id)
            ->where('is_active', true)
            ->exists();

        if ($existingActive) {
            throw ValidationException::withMessages([
                'global_number' => __('This worker is already linked to your business.'),
            ]);
        }

        $linkType = LinkType::from($data['link_type'] ?? LinkType::Permanent->value);
        $expiresAt = null;

        if ($linkType === LinkType::Temporary) {
            $expiresAt = isset($data['expires_at'])
                ? \Carbon\Carbon::parse($data['expires_at'])
                : now()->addDays(30);
        }

        return BusinessWorker::create([
            'business_id' => $business->id,
            'worker_id' => $worker->id,
            'link_type' => $linkType,
            'linked_at' => now(),
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }
}
