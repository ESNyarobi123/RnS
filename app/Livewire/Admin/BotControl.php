<?php

namespace App\Livewire\Admin;

use App\Models\BotSetting;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Bot Control')]
class BotControl extends Component
{
    public string $phoneNumber = '';

    public string $secretKey = '';

    public bool $isActive = false;

    public function mount(): void
    {
        $settings = BotSetting::current();

        if ($settings) {
            $this->phoneNumber = $settings->phone_number ?? '';
            $this->secretKey = $settings->secret_key ?? '';
            $this->isActive = $settings->is_active ?? false;
        }
    }

    public function save(): void
    {
        $this->validate([
            'phoneNumber' => 'required|string|max:20',
            'secretKey' => 'required|string|min:32',
        ]);

        $current = BotSetting::current();

        BotSetting::query()->updateOrCreate([
            'id' => $current?->id,
        ], [
            'phone_number' => $this->phoneNumber,
            'secret_key' => $this->secretKey,
            'is_active' => $this->isActive,
        ]);

        Flux::toast(variant: 'success', text: __('Bot settings saved successfully.'));
    }

    public function generateSecretKey(): void
    {
        $this->secretKey = Str::random(64);
        Flux::toast(variant: 'info', text: __('New secret key generated.'));
    }

    public function toggleBot(): void
    {
        $settings = BotSetting::current();

        if (! $settings) {
            Flux::toast(variant: 'danger', text: __('Save the bot settings first.'));

            return;
        }

        $settings->update(['is_active' => ! $settings->is_active]);
        $this->isActive = ! $this->isActive;

        $status = $this->isActive ? 'activated' : 'deactivated';
        Flux::toast(variant: 'success', text: __("Bot {$status} successfully."));
    }
}
