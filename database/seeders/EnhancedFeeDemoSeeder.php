<?php

namespace Database\Seeders;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnhancedFeeDemoSeeder extends Seeder
{
    /**
     * Run database seeds.
     */
    public function run(): void
    {
        // Get first organization for demo
        $organization = Organization::first();
        if (! $organization) {
            $this->command->error('No organization found. Please run organization seeder first.');

            return;
        }

        // Clear existing fees for this organization
        MemberFee::where('organization_id', $organization->id)->delete();

        // Get members for organization
        $members = Member::where('organization_id', $organization->id)->get();

        if ($members->isEmpty()) {
            $this->command->error('No members found. Please run member seeder first.');

            return;
        }

        $feeTypes = [
            'subscription' => [
                'Monthly Membership Fee',
                'Quarterly Membership Fee',
                'Annual Membership Fee',
                'Premium Membership',
                'Family Membership',
            ],
            'late_fee' => [
                'Late Payment Fee',
                'Overdue Charge',
                'Penalty Fee',
            ],
            'penalty' => [
                'Breach of Rules Penalty',
                'Facility Damage Fee',
                'No-Show Penalty',
            ],
            'additional_service' => [
                'Personal Training Session',
                'Equipment Rental',
                'Locker Rental',
                'Guest Pass',
                'Special Event Access',
                'Wellness Consultation',
            ],
            'event_fee' => [
                'Annual Gala Ticket',
                'Tournament Entry Fee',
                'Workshop Registration',
                'Networking Event',
                'Charity Dinner',
            ],
            'donation' => [
                'Building Fund Donation',
                'Equipment Fund Donation',
                'Scholarship Fund Donation',
                'Community Support',
            ],
        ];

        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'debit_card', 'check', 'online', 'mobile_money'];

        // Create realistic fee scenarios
        foreach ($members as $index => $member) {
            $memberFees = [];

            // Create 3-8 fees per member
            $feeCount = rand(3, 8);

            for ($i = 0; $i < $feeCount; $i++) {
                $feeType = array_rand($feeTypes);
                $descriptions = $feeTypes[$feeType];
                $description = $descriptions[array_rand($descriptions)];

                // Generate realistic amounts based on fee type
                $amount = $this->generateAmountByType($feeType);

                // Generate due date (some past, some future)
                $daysOffset = rand(-60, 90);
                $dueDate = now()->addDays($daysOffset);

                // Determine status
                $status = $this->determineStatus($dueDate, $daysOffset);

                $feeData = [
                    'organization_id' => $organization->id,
                    'member_id' => $member->id,
                    'fee_type' => $feeType,
                    'description' => $description,
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'notes' => $this->generateNotes($feeType, $status),
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now(),
                ];

                // Add payment details for paid fees
                if ($status === 'paid') {
                    $paidDate = $dueDate->copy()->addDays(rand(-5, 5));
                    $paidAmount = $amount; // Full payment for demo

                    $feeData['paid_date'] = $paidDate;
                    $feeData['paid_amount'] = $paidAmount;
                    $feeData['payment_method'] = $paymentMethods[array_rand($paymentMethods)];
                    $feeData['payment_reference'] = $this->generatePaymentReference();
                }

                $memberFees[] = $feeData;
            }

            // Insert fees for this member
            foreach ($memberFees as $feeData) {
                MemberFee::create($feeData);
            }
        }

        // Create some recurring fees
        $this->createRecurringFees($organization, $members);

        // Create some overdue fees with late fees
        $this->createOverdueScenarios($organization, $members);

        $this->command->info('Enhanced fee demo data created successfully!');
        $this->command->info('Created '.MemberFee::where('organization_id', $organization->id)->count().' fees across '.$members->count().' members.');
    }

    private function generateAmountByType(string $feeType): float
    {
        $ranges = [
            'subscription' => [25, 200],
            'late_fee' => [10, 50],
            'penalty' => [25, 150],
            'additional_service' => [15, 100],
            'event_fee' => [35, 250],
            'donation' => [10, 500],
        ];

        $range = $ranges[$feeType] ?? [10, 100];

        return round(rand($range[0] * 100, $range[1] * 100) / 100, 2);
    }

    private function determineStatus($dueDate, int $daysOffset): string
    {
        if ($daysOffset < -30) {
            // Long overdue - mix of paid and waived
            return rand(0, 10) > 3 ? 'paid' : 'waived';
        } elseif ($daysOffset < 0) {
            // Recently overdue - mix of pending and paid
            return rand(0, 10) > 6 ? 'paid' : 'overdue';
        } elseif ($daysOffset <= 7) {
            // Due soon - mostly pending
            return rand(0, 10) > 8 ? 'paid' : 'pending';
        } else {
            // Future due - mostly pending
            return 'pending';
        }
    }

    private function generateNotes(string $feeType, string $status): ?string
    {
        $notes = [
            'subscription' => [
                'Annual membership renewal',
                'Quarterly billing cycle',
                'Monthly subscription fee',
                'Premium membership benefits included',
            ],
            'late_fee' => [
                'Applied due to late payment',
                'Automatic late fee calculation',
                'Grace period exceeded',
            ],
            'penalty' => [
                'Violation of facility rules',
                'Damage to equipment',
                'No-show for scheduled appointment',
            ],
            'additional_service' => [
                'One-on-one training session',
                'Equipment rental for event',
                'Premium locker access',
                'Guest pass for family member',
            ],
            'event_fee' => [
                'Annual fundraising gala',
                'Tournament registration fee',
                'Professional workshop attendance',
                'Networking event participation',
            ],
            'donation' => [
                'Voluntary contribution',
                'Tax-deductible donation',
                'Community support fund',
            ],
        ];

        if ($status === 'waived') {
            return 'Waived by management - '.($notes[$feeType][array_rand($notes[$feeType])] ?? 'Special consideration');
        }

        return $notes[$feeType][array_rand($notes[$feeType])] ?? null;
    }

    private function generatePaymentReference(): string
    {
        $prefixes = ['PAY', 'TXN', 'REF', 'ID'];
        $prefix = $prefixes[array_rand($prefixes)];
        $number = strtoupper(Str::random(8));

        return $prefix.'-'.$number;
    }

    private function createRecurringFees(Organization $organization, $members): void
    {
        // Create recurring subscription fees for some members
        $recurringMembers = $members->random(min(5, $members->count()));

        foreach ($recurringMembers as $member) {
            // Create 3 months of subscription fees
            for ($month = 0; $month < 3; $month++) {
                MemberFee::create([
                    'organization_id' => $organization->id,
                    'member_id' => $member->id,
                    'fee_type' => 'subscription',
                    'description' => 'Monthly Membership Fee - '.now()->addMonths($month)->format('F Y'),
                    'amount' => rand(50, 150),
                    'due_date' => now()->addMonths($month)->startOfMonth()->addDays(15),
                    'status' => $month === 0 ? 'pending' : 'pending',
                    'notes' => 'Recurring monthly subscription',
                ]);
            }
        }
    }

    private function createOverdueScenarios(Organization $organization, $members): void
    {
        // Create some overdue fees with corresponding late fees
        $overdueMembers = $members->random(min(3, $members->count()));

        foreach ($overdueMembers as $member) {
            // Create original overdue fee
            $originalFee = MemberFee::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'fee_type' => 'subscription',
                'description' => 'Monthly Membership Fee',
                'amount' => rand(75, 125),
                'due_date' => now()->subDays(rand(15, 45)),
                'status' => 'overdue',
                'notes' => 'Overdue payment - late fees applied',
            ]);

            // Create corresponding late fee
            MemberFee::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'fee_type' => 'late_fee',
                'description' => 'Late fee for fee #'.$originalFee->id.' - '.$originalFee->description,
                'amount' => round($originalFee->amount * 0.05 + rand(5, 15), 2),
                'due_date' => now()->addDays(7),
                'status' => 'pending',
                'notes' => 'Automated late fee calculation: 5% of original amount + daily charge',
            ]);
        }
    }
}
