<?php

namespace App\Actions\Business;

use App\Enums\BusinessStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateBusiness
{
    /**
     * Validate and create a new business for a manager.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(User $manager, array $data): Business
    {
        if ($manager->businesses()->exists()) {
            throw ValidationException::withMessages([
                'business' => __('You already have a business. Only one business per manager is allowed.'),
            ]);
        }

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['restaurant', 'salon'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ])->validate();

        $business = Business::create([
            'user_id' => $manager->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => BusinessStatus::Active,
        ]);

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $business->uploadImage($data['logo']);
        }

        return $business;
    }
}
