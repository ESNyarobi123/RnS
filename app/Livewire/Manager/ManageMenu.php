<?php

namespace App\Livewire\Manager;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Menu Management')]
class ManageMenu extends Component
{
    use WithFileUploads;

    public $menuImage = null;

    public function uploadMenuImage(): void
    {
        $this->validate([
            'menuImage' => 'required|image|max:4096',
        ]);

        $business = $this->business();

        if ($business->menu_image) {
            Storage::disk('public')->delete($business->menu_image);
        }

        $business->update([
            'menu_image' => $this->menuImage->store('business-menu-images', 'public'),
        ]);

        $this->menuImage = null;

        Flux::toast(variant: 'success', text: __('Menu image uploaded successfully.'));
    }

    public function removeMenuImage(): void
    {
        $business = $this->business();

        if (! $business->menu_image) {
            return;
        }

        Storage::disk('public')->delete($business->menu_image);
        $business->update(['menu_image' => null]);

        Flux::toast(variant: 'success', text: __('Menu image removed.'));
    }

    private function business()
    {
        return Auth::user()->businesses()->firstOrFail();
    }

    public function render()
    {
        $business = $this->business();
        
        return view('livewire.manager.manage-menu', [
            'business' => $business,
            'categories' => $business->categories()->withCount('products')->orderBy('sort_order')->get(),
            'products' => $business->activeProducts()->with('category')->latest()->take(12)->get(),
            'title' => $business->isSalon() ? __('Service Menu') : __('Menu'),
        ]);
    }
}
