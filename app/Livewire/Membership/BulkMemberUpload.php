<?php

namespace App\Livewire\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class BulkMemberUpload extends Component
{
    use WithFileUploads;

    public $csvFile;

    public array $previewData = [];

    public array $columnMapping = [];

    public bool $showPreview = false;

    public bool $importInProgress = false;

    public string $uploadType = 'individual'; // individual, family, corporate

    public array $importResults = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    public array $availableColumns = [
        'title' => 'Title',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'postal_code' => 'Postal Code',
        'country' => 'Country',
        'join_date' => 'Join Date',
        'expiry_date' => 'Expiry Date',
        'notes' => 'Notes',
        'relationship' => 'Relationship (Family Members)',
        'primary_member_email' => 'Primary Member Email (Family Members)',
    ];

    protected $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        'uploadType' => 'required|in:individual,family,corporate',
        'columnMapping.*' => 'required|string',
    ];

    public function uploadCsv()
    {
        $this->validate();

        $this->previewData = [];
        $this->columnMapping = [];
        $this->showPreview = false;

        try {
            $filePath = $this->csvFile->getRealPath();
            $fileHandle = fopen($filePath, 'r');

            if (! $fileHandle) {
                $this->dispatch('show-notification', message: 'Could not read the uploaded file', type: 'error');

                return;
            }

            // Read header row
            $header = fgetcsv($fileHandle);
            if (! $header) {
                $this->dispatch('show-notification', message: 'CSV file is empty or invalid', type: 'error');
                fclose($fileHandle);

                return;
            }

            // Clean header names
            $header = array_map('trim', $header);

            // Auto-detect column mapping
            $this->autoDetectColumnMapping($header);

            // Read preview data (first 10 rows)
            $previewRows = [];
            $rowNumber = 2; // Start from 2 since header is row 1

            while (($row = fgetcsv($fileHandle)) !== false && count($previewRows) < 10) {
                if (count($header) === count($row)) {
                    $rowData = array_combine($header, $row);
                } else {
                    $rowData = $row;
                }
                $previewRows[] = [
                    'row_number' => $rowNumber,
                    'data' => $rowData,
                    'mapped_data' => $this->mapRowData($rowData),
                    'errors' => $this->validateRow($rowData, $rowNumber),
                ];
                $rowNumber++;
            }

            fclose($fileHandle);

            $this->previewData = $previewRows;
            $this->showPreview = true;

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error processing CSV: '.$e->getMessage(), type: 'error');
        }
    }

    private function autoDetectColumnMapping(array $header): void
    {
        $mapping = [];

        foreach ($header as $column) {
            $normalizedColumn = strtolower(str_replace([' ', '_', '-'], '', $column));

            foreach ($this->availableColumns as $key => $label) {
                $normalizedLabel = strtolower(str_replace([' ', '_', '-', '(', ')'], '', $label));

                // Check for exact match or partial match
                if ($normalizedColumn === $normalizedLabel ||
                    str_contains($normalizedColumn, $normalizedLabel) ||
                    str_contains($normalizedLabel, $normalizedColumn)) {
                    $mapping[$column] = $key;
                    break;
                }
            }
        }

        $this->columnMapping = $mapping;
    }

    private function mapRowData(array $row): array
    {
        $mapped = [];

        foreach ($this->columnMapping as $csvColumn => $dbColumn) {
            $value = $row[$csvColumn] ?? '';
            $mapped[$dbColumn] = trim($value);
        }

        return $mapped;
    }

    private function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $mappedData = $this->mapRowData($row);

        // Validate required fields
        if (empty($mappedData['first_name'])) {
            $errors[] = 'First name is required';
        }

        if (empty($mappedData['last_name'])) {
            $errors[] = 'Last name is required';
        }

        if (empty($mappedData['email'])) {
            $errors[] = 'Email is required';
        } elseif (! filter_var($mappedData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        // Validate email uniqueness
        if (! empty($mappedData['email'])) {
            $existingMember = Member::where('email', $mappedData['email'])
                ->where('organization_id', auth()->user()->current_organization_id)
                ->first();

            if ($existingMember) {
                $errors[] = 'Email already exists';
            }
        }

        // Validate gender
        if (! empty($mappedData['gender']) && ! in_array($mappedData['gender'], ['male', 'female', 'other'])) {
            $errors[] = 'Invalid gender value';
        }

        // Validate dates
        if (! empty($mappedData['date_of_birth'])) {
            if (! strtotime($mappedData['date_of_birth'])) {
                $errors[] = 'Invalid date of birth format';
            }
        }

        if (! empty($mappedData['join_date'])) {
            if (! strtotime($mappedData['join_date'])) {
                $errors[] = 'Invalid join date format';
            }
        }

        // Family-specific validations
        if ($this->uploadType === 'family') {
            if (empty($mappedData['relationship'])) {
                $errors[] = 'Relationship is required for family uploads';
            }

            if (empty($mappedData['primary_member_email'])) {
                $errors[] = 'Primary member email is required for family members';
            }
        }

        return $errors;
    }

    public function confirmImport()
    {
        $this->importInProgress = true;
        $this->importResults = ['success' => 0, 'failed' => 0, 'errors' => []];

        try {
            $filePath = $this->csvFile->getRealPath();
            $fileHandle = fopen($filePath, 'r');

            // Skip header row
            $header = fgetcsv($fileHandle);

            $rowNumber = 2;

            while (($row = fgetcsv($fileHandle)) !== false) {
                if (count($header) === count($row)) {
                    $rowData = array_combine($header, $row);
                } else {
                    $rowData = $row;
                }

                try {
                    $this->processRow($rowData);
                    $this->importResults['success']++;
                } catch (\Exception $e) {
                    $this->importResults['failed']++;
                    $this->importResults['errors'][] = "Row {$rowNumber}: ".$e->getMessage();
                }

                $rowNumber++;
            }

            fclose($fileHandle);

            $this->dispatch('show-notification',
                message: "Import completed: {$this->importResults['success']} successful, {$this->importResults['failed']} failed",
                type: $this->importResults['failed'] > 0 ? 'warning' : 'success'
            );

            // Reset form
            $this->reset(['csvFile', 'previewData', 'columnMapping', 'showPreview']);

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Import failed: '.$e->getMessage(), type: 'error');
        } finally {
            $this->importInProgress = false;
        }
    }

    private function processRow(array $rowData): void
    {
        $mappedData = $this->mapRowData($rowData);
        $organizationId = auth()->user()->current_organization_id;

        if ($this->uploadType === 'family') {
            // Process as family member
            $primaryMember = Member::where('email', $mappedData['primary_member_email'])
                ->where('organization_id', $organizationId)
                ->firstOrFail();

            FamilyMember::create([
                'organization_id' => $organizationId,
                'primary_member_id' => $primaryMember->id,
                'relationship' => $mappedData['relationship'],
                'title' => $mappedData['title'] ?? null,
                'first_name' => $mappedData['first_name'],
                'last_name' => $mappedData['last_name'],
                'date_of_birth' => ! empty($mappedData['date_of_birth']) ? date('Y-m-d', strtotime($mappedData['date_of_birth'])) : null,
                'gender' => $mappedData['gender'] ?? 'other',
                'barcode_number' => 'FAM-'.$organizationId.'-'.Str::random(6),
                'status' => 'active',
                'notes' => $mappedData['notes'] ?? null,
            ]);
        } else {
            // Process as individual member
            $member = Member::create([
                'organization_id' => $organizationId,
                'title' => $mappedData['title'] ?? null,
                'first_name' => $mappedData['first_name'],
                'last_name' => $mappedData['last_name'],
                'email' => $mappedData['email'],
                'phone' => $mappedData['phone'] ?? null,
                'date_of_birth' => ! empty($mappedData['date_of_birth']) ? date('Y-m-d', strtotime($mappedData['date_of_birth'])) : null,
                'gender' => $mappedData['gender'] ?? 'other',
                'address' => $mappedData['address'] ?? null,
                'city' => $mappedData['city'] ?? null,
                'state' => $mappedData['state'] ?? null,
                'postal_code' => $mappedData['postal_code'] ?? null,
                'country' => $mappedData['country'] ?? null,
                'join_date' => ! empty($mappedData['join_date']) ? date('Y-m-d', strtotime($mappedData['join_date'])) : now(),
                'expiry_date' => ! empty($mappedData['expiry_date']) ? date('Y-m-d', strtotime($mappedData['expiry_date'])) : null,
                'notes' => $mappedData['notes'] ?? null,
                'membership_number' => 'MEM'.str_pad($organizationId, 4, '0', STR_PAD_LEFT).Str::random(4),
                'barcode_number' => 'BC'.Str::random(6),
                'status' => 'active',
            ]);
        }
    }

    public function downloadTemplate()
    {
        $templates = [
            'individual' => [
                'title',
                'first_name',
                'last_name',
                'email',
                'phone',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'join_date',
                'expiry_date',
                'notes',
            ],
            'family' => [
                'title',
                'first_name',
                'last_name',
                'email',
                'phone',
                'date_of_birth',
                'gender',
                'relationship',
                'primary_member_email',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'notes',
            ],
            'corporate' => [
                'title',
                'first_name',
                'last_name',
                'email',
                'phone',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'join_date',
                'expiry_date',
                'department',
                'employee_id',
                'notes',
            ],
        ];

        $filename = "membership_{$this->uploadType}_template.csv";
        $headers = $templates[$this->uploadType] ?? $templates['individual'];

        // Create CSV content
        $csv = implode(',', $headers)."\n";

        // Add sample data
        if ($this->uploadType === 'individual') {
            $csv .= "Mr,John,Doe,john.doe@example.com,+1-555-0123,1985-03-15,male,123 Main St,Springfield,IL,62701,USA,2024-01-01,2025-01-01,Sample member\n";
        } elseif ($this->uploadType === 'family') {
            $csv .= "Mrs,Jane,Doe,jane.doe@example.com,+1-555-0124,1987-06-22,female,spouse,john.doe@example.com,123 Main St,Springfield,IL,62701,USA,Family member\n";
        } else {
            $csv .= "Dr,Alice,Smith,alice.smith@company.com,+1-555-0125,1990-09-10,female,456 Oak Ave,Springfield,IL,62702,USA,2024-01-01,2025-01-01,IT,EMP001,Corporate member\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename);
    }

    public function cancelImport()
    {
        $this->reset(['csvFile', 'previewData', 'columnMapping', 'showPreview', 'importResults']);
    }

    public function mount(): void
    {
        try {
            $this->authorize('membership.create_members');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.membership.bulk-member-upload');
    }
}
