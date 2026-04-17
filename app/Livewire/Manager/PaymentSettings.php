<?php

namespace App\Livewire\Manager;

use App\Models\PaymentSetting;
use App\Services\SelcomService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Payment Settings')]
class PaymentSettings extends Component
{
    public string $apiKey = '';

    public string $apiSecret = '';

    public string $vendor = '';

    public string $environment = 'sandbox';

    public bool $isActive = false;

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function setting()
    {
        return $this->business?->paymentSettings()
            ->where('provider', 'selcom')
            ->first();
    }

    public function mount(): void
    {
        if ($this->setting) {
            $this->apiKey = $this->setting->api_key ?? '';
            $this->apiSecret = $this->setting->api_secret ?? '';
            $config = $this->setting->config ?? [];
            $this->vendor = $config['vendor'] ?? '';
            $this->environment = $config['environment'] ?? 'sandbox';
            $this->isActive = $this->setting->is_active;
        }
    }

    public function save(): void
    {
        $this->validate([
            'apiKey' => 'required|string',
            'apiSecret' => 'required|string',
            'vendor' => 'required|string',
            'environment' => 'required|in:sandbox,production',
        ]);

        $this->business->paymentSettings()->updateOrCreate(
            ['provider' => 'selcom'],
            [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'config' => [
                    'vendor' => $this->vendor,
                    'environment' => $this->environment,
                ],
                'is_active' => $this->isActive,
            ]
        );

        unset($this->setting);

        Flux::toast(variant: 'success', text: __('Payment settings saved.'));
    }

    public function testConnection(): void
    {
        $selcom = SelcomService::forBusiness($this->business);

        if (! $selcom) {
            Flux::toast(variant: 'danger', text: __('Save your credentials first and make sure settings are active.'));

            return;
        }

        $result = $selcom->orderStatus('TEST-'.now()->format('YmdHis'));

        if ($result['success'] || str_contains($result['error'] ?? '', 'not found')) {
            Flux::toast(variant: 'success', text: __('Connection successful! Selcom API is reachable.'));
        } else {
            Flux::toast(variant: 'danger', text: __('Connection failed: :error', ['error' => $result['error'] ?? 'Unknown']));
        }
    }
}
