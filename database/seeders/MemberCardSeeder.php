<?php

namespace Database\Seeders;

use App\Models\Membership\Member;
use App\Models\Membership\MemberCard;
use Illuminate\Database\Seeder;

class MemberCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing members
        $members = Member::with('organization')->get();

        if ($members->isEmpty()) {
            $this->command->info('No members found. Please run MemberSeeder first.');

            return;
        }

        foreach ($members as $member) {
            // Create 1-2 cards per member
            $cardCount = rand(1, 2);

            for ($i = 0; $i < $cardCount; $i++) {
                $cardType = ['standard', 'premium', 'family', 'corporate'][array_rand(['standard', 'premium', 'family', 'corporate'])];
                $template = match ($cardType) {
                    'premium' => 'premium',
                    'corporate' => 'corporate',
                    'family' => 'family',
                    default => ['modern', 'classic', 'minimal'][array_rand(['modern', 'classic', 'minimal'])],
                };

                $status = ['active', 'expired', 'lost', 'damaged'][array_rand(['active', 'expired', 'lost', 'damaged'])];

                MemberCard::create([
                    'organization_id' => $member->organization_id,
                    'member_id' => $member->id,
                    'card_number' => 'CARD-'.strtoupper(uniqid()),
                    'card_type' => $cardType,
                    'template' => $template,
                    'status' => $status,
                    'issue_date' => now()->subMonths(rand(1, 12)),
                    'expiry_date' => $status === 'expired' ?
                        now()->subMonths(rand(1, 6)) :
                        now()->addMonths(rand(1, 12)),
                    'qr_code_path' => 'qr-codes/'.uniqid().'.png',
                    'barcode_path' => 'barcodes/'.uniqid().'.png',
                    'design_settings' => [
                        'primary_color' => $this->getRandomColor(),
                        'secondary_color' => $this->getRandomColor(),
                        'font_family' => ['Arial', 'Helvetica', 'Times New Roman', 'Georgia'][array_rand(['Arial', 'Helvetica', 'Times New Roman', 'Georgia'])],
                        'layout' => ['horizontal', 'vertical'][array_rand(['horizontal', 'vertical'])],
                        'show_photo' => rand(0, 1) === 1,
                        'show_qr_code' => true,
                        'show_barcode' => true,
                        'show_expiry' => rand(0, 1) === 1,
                    ],
                    'notes' => rand(0, 1) === 1 ? 'Demo card for '.$member->full_name : null,
                    'print_count' => rand(0, 5),
                    'last_printed_at' => rand(0, 1) === 1 ? now()->subDays(rand(1, 30)) : null,
                ]);
            }
        }

        $this->command->info('Member cards seeded successfully!');
    }

    private function getRandomColor(): string
    {
        $colors = [
            '#1e40af', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b',
            '#ef4444', '#8b5cf6', '#ec4899', '#f97316', '#14b8a6',
        ];

        return $colors[array_rand($colors)];
    }
}
