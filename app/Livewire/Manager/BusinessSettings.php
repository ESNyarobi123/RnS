<?php

namespace App\Livewire\Manager;

use App\Models\BotSetting;
use App\Services\QrCodeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Business Settings')]
class BusinessSettings extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $description = '';

    public string $address = '';

    public string $phone = '';

    public $logo = null;

    public $menuImage = null;

    public function mount(): void
    {
        $business = $this->business;
        if ($business) {
            $this->name = $business->name;
            $this->description = $business->description ?? '';
            $this->address = $business->address ?? '';
            $this->phone = $business->phone ?? '';
        }
    }

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function botSetting(): ?BotSetting
    {
        return BotSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'menuImage' => 'nullable|image|max:4096',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
        ];

        if ($this->logo) {
            if ($this->business->logo) {
                Storage::disk('public')->delete($this->business->logo);
            }
            $data['logo'] = $this->logo->store('logos', 'public');
        }

        if ($this->menuImage) {
            if ($this->business->menu_image) {
                Storage::disk('public')->delete($this->business->menu_image);
            }

            $data['menu_image'] = $this->menuImage->store('business-menu-images', 'public');
        }

        $this->business->update($data);
        $this->logo = null;
        $this->menuImage = null;

        unset($this->business);

        Flux::toast(variant: 'success', text: __('Business updated.'));
    }

    public function removeLogo(): void
    {
        if ($this->business->logo) {
            Storage::disk('public')->delete($this->business->logo);
            $this->business->update(['logo' => null]);
            unset($this->business);
            Flux::toast(variant: 'success', text: __('Logo removed.'));
        }
    }

    public function removeMenuImage(): void
    {
        if (! $this->business->menu_image) {
            return;
        }

        Storage::disk('public')->delete($this->business->menu_image);
        $this->business->update(['menu_image' => null]);
        unset($this->business);

        Flux::toast(variant: 'success', text: __('Menu image removed.'));
    }

    public function generateBotAssets(QrCodeService $qrCodeService): void
    {
        if (! $this->botSetting) {
            Flux::toast(variant: 'danger', text: __('Ask the admin to activate the WhatsApp bot first.'));

            return;
        }

        $qrCodeService->generateBusinessQrCode($this->business);

        unset($this->business);

        Flux::toast(variant: 'success', text: __('Business QR and WhatsApp code generated.'));
    }
}
