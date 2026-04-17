<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in([UserRole::Manager->value, UserRole::Worker->value])],
        ])->validate();

        return DB::transaction(function () use ($input) {
            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'phone' => $input['phone'] ?? null,
                'role' => $input['role'],
            ]);
        });
    }
}
