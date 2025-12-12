<?php

namespace App\Livewire\Membership;

use Livewire\Component;
use Livewire\WithFileUploads;

class UserRegistrationSystem extends Component
{
    use WithFileUploads;

    public string $activeTab = 'individual';

    // Individual registration
    public array $individualForm = [
        'title' => '',
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
        'country' => '',
        'subscription_plan' => 'individual',
        'emergency_contact_name' => '',
        'emergency_contact_phone' => '',
    ];

    public $individualPhoto;

    public $individualPhotoPreview;

    // Bulk upload
    public $csvFile;

    public array $csvPreview = [];

    public bool $showPreview = false;

    public array $uploadProgress = [
        'total' => 0,
        'processed' => 0,
        'successful' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    protected array $rules = [
        'individualForm.title' => 'required|in:Mr,Mrs,Ms,Dr',
        'individualForm.first_name' => 'required|string|min:2',
        'individualForm.last_name' => 'required|string|min:2',
        'individualForm.email' => 'required|email',
        'individualForm.phone' => 'required|string|min:10',
        'individualForm.address' => 'required|string|min:5',
        'individualForm.city' => 'required|string|min:2',
        'individualForm.state' => 'required|string|min:2',
        'individualForm.postal_code' => 'required|string|min:3',
        'individualForm.country' => 'required|string|min:2',
        'individualForm.subscription_plan' => 'required|in:corporate,family,individual,sports,social',
        'individualForm.emergency_contact_name' => 'required|string|min:2',
        'individualForm.emergency_contact_phone' => 'required|string|min:10',
        'individualPhoto' => 'nullable|image|max:2048',
        'csvFile' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
    ];

    public function updatedIndividualPhoto(): void
    {
        $this->validate(['individualPhoto' => 'nullable|image|max:2048']);
        $this->individualPhotoPreview = $this->individualPhoto->temporaryUrl();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetIndividualForm();
        $this->resetBulkUpload();
    }

    public function registerIndividual(): void
    {
        $this->validate([
            'individualForm.title' => 'required|in:Mr,Mrs,Ms,Dr',
            'individualForm.first_name' => 'required|string|min:2',
            'individualForm.last_name' => 'required|string|min:2',
            'individualForm.email' => 'required|email',
            'individualForm.phone' => 'required|string|min:10',
            'individualForm.address' => 'required|string|min:5',
            'individualForm.city' => 'required|string|min:2',
            'individualForm.state' => 'required|string|min:2',
            'individualForm.postal_code' => 'required|string|min:3',
            'individualForm.country' => 'required|string|min:2',
            'individualForm.subscription_plan' => 'required|in:corporate,family,individual,sports,social',
            'individualForm.emergency_contact_name' => 'required|string|min:2',
            'individualForm.emergency_contact_phone' => 'required|string|min:10',
            'individualPhoto' => 'nullable|image|max:2048',
        ]);

        // Simulate registration
        usleep(1000000);

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Member registered successfully!',
        ]);

        $this->resetIndividualForm();
    }

    public function uploadCSV(): void
    {
        $this->validate(['csvFile' => 'required|file|mimes:csv,txt|max:10240']);

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        $this->csvPreview = [];
        $headers = fgetcsv($file);
        $rowCount = 0;

        while (($row = fgetcsv($file)) !== false && $rowCount < 5) {
            $this->csvPreview[] = array_combine($headers, $row);
            $rowCount++;
        }

        // Count total rows
        rewind($file);
        fgetcsv($file); // Skip headers
        $this->uploadProgress['total'] = 0;
        while (fgetcsv($file) !== false) {
            $this->uploadProgress['total']++;
        }
        $this->uploadProgress['total'] += $rowCount;

        fclose($file);
        $this->showPreview = true;
    }

    public function processBulkUpload(): void
    {
        $this->uploadProgress['processed'] = 0;
        $this->uploadProgress['successful'] = 0;
        $this->uploadProgress['failed'] = 0;
        $this->uploadProgress['errors'] = [];

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        fgetcsv($file); // Skip headers

        while (($row = fgetcsv($file)) !== false) {
            $this->uploadProgress['processed']++;

            try {
                // Simulate processing
                usleep(100000); // 0.1 second per record

                // Validate required fields
                if (empty($row[0]) || empty($row[1])) { // Assuming first_name, last_name
                    throw new \Exception('Missing required fields');
                }

                $this->uploadProgress['successful']++;

            } catch (\Exception $e) {
                $this->uploadProgress['failed']++;
                $this->uploadProgress['errors'][] = "Row {$this->uploadProgress['processed']}: ".$e->getMessage();
            }

            // Update progress every 10 records
            if ($this->uploadProgress['processed'] % 10 === 0) {
                $this->dispatch('upload-progress', $this->uploadProgress);
            }
        }

        fclose($file);

        $this->dispatch('show-notification', [
            'type' => $this->uploadProgress['failed'] > 0 ? 'warning' : 'success',
            'message' => "Bulk upload completed. {$this->uploadProgress['successful']} successful, {$this->uploadProgress['failed']} failed.",
        ]);
    }

    public function downloadTemplate(): void
    {
        $template = [
            ['title', 'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'subscription_plan', 'emergency_contact_name', 'emergency_contact_phone'],
            ['Mr', 'John', 'Doe', 'john.doe@example.com', '+1234567890', '123 Main St', 'New York', 'NY', '10001', 'USA', 'individual', 'Jane Doe', '+1234567891'],
            ['Mrs', 'Jane', 'Smith', 'jane.smith@example.com', '+1234567892', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'USA', 'family', 'John Smith', '+1234567893'],
        ];

        $filename = 'member_registration_template.csv';
        $handle = fopen('php://memory', 'w');

        foreach ($template as $row) {
            fputcsv($handle, $row);
        }

        fseek($handle, 0);
        $content = stream_get_contents($handle);
        fclose($handle);

        $this->dispatch('download-file', [
            'filename' => $filename,
            'content' => $content,
        ]);
    }

    public function resetIndividualForm(): void
    {
        $this->individualForm = [
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
            'subscription_plan' => 'individual',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
        ];
        $this->individualPhoto = null;
        $this->individualPhotoPreview = null;
    }

    public function resetBulkUpload(): void
    {
        $this->csvFile = null;
        $this->csvPreview = [];
        $this->showPreview = false;
        $this->uploadProgress = [
            'total' => 0,
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }

    public function render()
    {
        return view('livewire.membership.user-registration-system');
    }
}
