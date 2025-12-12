<?php

namespace App\Livewire\Membership;

use Livewire\Component;
use Livewire\WithFileUploads;

class SimpleRegistration extends Component
{
    use WithFileUploads;

    public array $formData = [
        'title' => '',
        'first_name' => '',
        'last_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
        'country' => '',
        'subscription_plan' => 'individual',
        'family_members_count' => 1,
        'emergency_contact_name' => '',
        'emergency_contact_phone' => '',
        'payment_method' => 'card',
        'notes' => '',
    ];

    public $photo;

    public $photoPreview;

    public array $countryClubPlans = [
        'corporate' => [
            'name' => 'Corporate Membership',
            'price' => 1000000,
            'period' => 'yearly',
            'features' => [
                'Unlimited access to all facilities',
                'Priority booking for events',
                'Complimentary guest passes (10 per month)',
                'Dedicated account manager',
                'Corporate event hosting privileges',
            ],
            'family_included' => 10,
        ],
        'family' => [
            'name' => 'Family Membership',
            'price' => 600000,
            'period' => 'yearly',
            'features' => [
                'Access to all family facilities',
                'Kids club access',
                'Family events priority',
                'Swimming pool access',
                'Tennis court booking',
            ],
            'family_included' => 4,
        ],
        'individual' => [
            'name' => 'Individual Membership',
            'price' => 300000,
            'period' => 'yearly',
            'features' => [
                'Full gym access',
                'Swimming pool access',
                'Tennis court booking',
                'Restaurant discounts',
                'Monthly newsletter',
            ],
            'family_included' => 1,
        ],
        'sports' => [
            'name' => 'Sports Membership',
            'price' => 25000,
            'period' => 'monthly',
            'features' => [
                'Gym and fitness center access',
                'All sports facilities',
                'Personal trainer discount',
                'Sports equipment rental',
                'Tournament participation',
            ],
            'family_included' => 2,
        ],
        'social' => [
            'name' => 'Social Membership',
            'price' => 150000,
            'period' => 'yearly',
            'features' => [
                'Restaurant and bar access',
                'Social events invitation',
                'Dining discounts',
                'Club house access',
                'New year celebration',
            ],
            'family_included' => 2,
        ],
    ];

    public bool $showSuccess = false;

    public bool $showFamilyMembers = false;

    public array $familyMembers = [];

    protected array $rules = [
        'formData.title' => 'required|in:Mr,Mrs,Ms,Dr',
        'formData.first_name' => 'required|string|min:2',
        'formData.last_name' => 'required|string|min:2',
        'formData.date_of_birth' => 'required|date|before:today',
        'formData.gender' => 'required|in:male,female,other',
        'formData.email' => 'required|email',
        'formData.phone' => 'required|string|min:10',
        'formData.address' => 'required|string|min:5',
        'formData.city' => 'required|string|min:2',
        'formData.state' => 'required|string|min:2',
        'formData.postal_code' => 'required|string|min:3',
        'formData.country' => 'required|string|min:2',
        'formData.subscription_plan' => 'required|in:corporate,family,individual,sports,social',
        'formData.family_members_count' => 'required|integer|min:1|max:20',
        'formData.emergency_contact_name' => 'required|string|min:2',
        'formData.emergency_contact_phone' => 'required|string|min:10',
        'formData.payment_method' => 'required|in:card,cash,bank,cheque',
        'photo' => 'nullable|image|max:2048', // 2MB max
    ];

    public function updatedPhoto(): void
    {
        $this->validate(['photo' => 'nullable|image|max:2048']);
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function updatedFormDataSubscriptionPlan($value): void
    {
        $plan = $this->countryClubPlans[$value] ?? null;
        if ($plan && $plan['family_included'] > 1) {
            $this->showFamilyMembers = true;
            $this->formData['family_members_count'] = $plan['family_included'];
        } else {
            $this->showFamilyMembers = false;
            $this->formData['family_members_count'] = 1;
        }
    }

    public function addFamilyMember(): void
    {
        $this->familyMembers[] = [
            'name' => '',
            'relationship' => '',
            'date_of_birth' => '',
            'gender' => '',
        ];
    }

    public function removeFamilyMember($index): void
    {
        unset($this->familyMembers[$index]);
        $this->familyMembers = array_values($this->familyMembers);
    }

    public function register(): void
    {
        $this->validate();

        // Simulate registration process
        usleep(1000000); // 1 second delay to simulate processing

        // Handle photo upload
        if ($this->photo) {
            $photoPath = $this->photo->store('member-photos', 'public');
            // In real implementation, save this to database
        }

        $this->showSuccess = true;

        // Reset form after 3 seconds
        $this->dispatch('reset-form');
    }

    public function resetForm(): void
    {
        $this->formData = [
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
            'subscription_plan' => 'individual',
            'family_members_count' => 1,
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'payment_method' => 'card',
            'notes' => '',
        ];
        $this->photo = null;
        $this->photoPreview = null;
        $this->familyMembers = [];
        $this->showFamilyMembers = false;
        $this->showSuccess = false;
    }

    public function render()
    {
        return view('livewire.membership.simple-registration');
    }
}
