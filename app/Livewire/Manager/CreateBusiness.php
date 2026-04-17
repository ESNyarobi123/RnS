<?php

namespace App\Livewire\Manager;

use App\Actions\Business\CreateBusiness as CreateBusinessAction;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Create Business')]
class CreateBusiness extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|in:restaurant,salon')]
    public string $type = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|string|max:500')]
    public string $address = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|image|max:2048')]
    public $logo = null;

    public function save(CreateBusinessAction $action): void
    {
        $this->validate();

        if (Auth::user()->businesses()->exists()) {
            Flux::toast(variant: 'danger', text: __('You already have a business.'));

            return;
        }

        $action->execute(Auth::user(), [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description ?: null,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'logo' => $this->logo,
        ]);

        Flux::toast(variant: 'success', text: __('Business created successfully!'));

        $this->redirect(route('manager.dashboard'));
    }
}
