<?php

// database/seeders/ConsolidatedDemoSeeder.php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Permissions\MembershipPermissions;
use App\Roles\MembershipRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ConsolidatedDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating comprehensive consolidated demo...');

        // Create demo admin user
        $admin = $this->createAdminUser();

        // Create organization structure
        $organization = $this->createOrganization();

        // Create organization units
        $units = $this->createOrganizationUnits($organization);

        // Attach admin to organization
        $this->attachUserToOrganization($admin, $organization, $units['Head Office']);

        // Create employees
        $employees = $this->createEmployees($organization, $units);

        // Create membership demo data
        $this->createMembershipDemoData($organization);

        $this->command->info('🎉 Consolidated demo completed!');
        $this->command->info('📧 Admin Login: admin@demo.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('👥 Membership Manager: membership.manager@demo.com');
        $this->command->info('🏢 Membership Staff: membership.staff@demo.com');
        $this->command->info('🏪 Front Desk: frontdesk@demo.com');
        $this->command->info('💼 Created '.$employees->count().' employees');
        $this->command->info('👥 Created membership demo data');
    }

    protected function createAdminUser(): User
    {
        $admin = User::where('email', 'admin@demo.com')->first();

        if (! $admin) {
            $admin = User::factory()->create([
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password'),
            ]);
            $this->command->info('✅ Created demo admin user');
        } else {
            $this->command->info('✅ Demo admin user already exists');
        }

        return $admin;
    }

    protected function createOrganization(): Organization
    {
        $organization = Organization::where('name', 'Demo Corporation')->first();

        if (! $organization) {
            $organization = Organization::create([
                'name' => 'Demo Corporation',
                'description' => 'Demo Corporation for testing and development',
                'is_active' => true,
            ]);
            $this->command->info('✅ Created demo organization: Demo Corporation');
        } else {
            $this->command->info('✅ Demo organization already exists: Demo Corporation');
        }

        return $organization;
    }

    protected function createOrganizationUnits(Organization $organization): Collection
    {
        $units = collect();
        $unitData = [
            ['name' => 'Head Office', 'type' => 'department'],
            ['name' => 'Sales Department', 'type' => 'department'],
            ['name' => 'Warehouse Department', 'type' => 'department'],
            ['name' => 'Accounting Department', 'type' => 'department'],
            ['name' => 'HR Department', 'type' => 'department'],
        ];

        foreach ($unitData as $data) {
            $unit = OrganizationUnit::where('organization_id', $organization->id)
                ->where('name', $data['name'])
                ->first();

            if (! $unit) {
                $unit = OrganizationUnit::create([
                    'organization_id' => $organization->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'parent_id' => null,
                ]);
                $this->command->info('✅ Created unit: '.$data['name']);
            } else {
                $this->command->info('✅ Unit already exists: '.$data['name']);
            }

            $units->put($data['name'], $unit);
        }

        $this->command->info('✅ Processed '.$units->count().' organization units');

        return $units;
    }

    protected function attachUserToOrganization(User $user, Organization $organization, OrganizationUnit $unit): void
    {
        $exists = $user->organizations()->where('organization_id', $organization->id)->exists();

        if (! $exists) {
            $user->organizations()->attach($organization->id, [
                'roles' => json_encode(['admin']),
                'organization_unit_id' => $unit->id,
                'position' => 'Administrator',
            ]);
            $this->command->info('✅ Attached admin user to organization');
        } else {
            $this->command->info('✅ Admin user already attached to organization');
        }

        // Grant admin user ALL membership permissions
        $allMembershipPermissions = [
            MembershipPermissions::VIEW_MEMBERS,
            MembershipPermissions::CREATE_MEMBERS,
            MembershipPermissions::EDIT_MEMBERS,
            MembershipPermissions::DELETE_MEMBERS,
            MembershipPermissions::MANAGE_MEMBERS,
            MembershipPermissions::VIEW_SUBSCRIPTIONS,
            MembershipPermissions::CREATE_SUBSCRIPTIONS,
            MembershipPermissions::EDIT_SUBSCRIPTIONS,
            MembershipPermissions::DELETE_SUBSCRIPTIONS,
            MembershipPermissions::MANAGE_SUBSCRIPTIONS,
            MembershipPermissions::RENEW_SUBSCRIPTIONS,
            MembershipPermissions::CANCEL_SUBSCRIPTIONS,
            MembershipPermissions::VIEW_FEES,
            MembershipPermissions::CREATE_FEES,
            MembershipPermissions::EDIT_FEES,
            MembershipPermissions::DELETE_FEES,
            MembershipPermissions::MANAGE_FEES,
            MembershipPermissions::PROCESS_PAYMENTS,
            MembershipPermissions::WAIVE_FEES,
            MembershipPermissions::PRINT_CARDS,
            MembershipPermissions::DESIGN_CARDS,
            MembershipPermissions::BATCH_PRINT_CARDS,
            MembershipPermissions::VIEW_REPORTS,
            MembershipPermissions::GENERATE_REPORTS,
            MembershipPermissions::EXPORT_DATA,
            MembershipPermissions::VIEW_DASHBOARD,
            MembershipPermissions::ADMIN,
        ];

        foreach ($allMembershipPermissions as $permission) {
            try {
                $user->givePermissionTo($permission, $organization);
                $this->command->info('✅ Granted admin permission: '.$permission);
            } catch (\Exception $e) {
                $this->command->error('❌ Failed to grant admin permission "'.$permission.'": '.$e->getMessage());
            }
        }
    }

    protected function createEmployees(Organization $organization, Collection $units): Collection
    {
        $employees = collect();
        $employeeData = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@democorp.com',
                'phone' => '+1-555-0101',
                'basic_salary' => 75000,
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@democorp.com',
                'phone' => '+1-555-0102',
                'basic_salary' => 65000,
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@democorp.com',
                'phone' => '+1-555-0103',
                'basic_salary' => 55000,
            ],
        ];

        foreach ($employeeData as $data) {
            $employee = Employee::where('organization_id', $organization->id)
                ->where('email', $data['email'])
                ->first();

            if (! $employee) {
                $employee = Employee::create([
                    'organization_id' => $organization->id,
                    'organization_unit_id' => $units['Head Office']->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'basic_salary' => $data['basic_salary'],
                    'is_active' => true,
                ]);
                $this->command->info('✅ Created employee: '.$data['first_name'].' '.$data['last_name']);
            } else {
                $this->command->info('✅ Employee already exists: '.$data['first_name'].' '.$data['last_name']);
            }

            $employees->put($data['email'], $employee);
        }

        $this->command->info('✅ Created '.$employees->count().' employees');

        return $employees;
    }

    protected function createMembershipDemoData(Organization $organization): void
    {
        $this->command->info('👥 Creating membership demo data...');

        // Create subscription plans
        $plans = $this->createSubscriptionPlans($organization);

        // Create members
        $members = $this->createMembers($organization);

        // Create family members
        $this->createFamilyMembers($organization, $members);

        // Create member subscriptions
        $this->createMemberSubscriptions($organization, $members, $plans);

        // Create member fees
        $this->createMemberFees($organization, $members);

        // Create membership users with roles
        $this->createMembershipUsers($organization);

        $this->command->info('✅ Membership demo data created successfully!');
    }

    protected function createSubscriptionPlans(Organization $organization): Collection
    {
        $plans = collect();
        $planData = [
            [
                'name' => 'Basic Membership',
                'description' => 'Access to gym facilities during regular hours',
                'plan_type' => 'individual',
                'billing_frequency' => 'monthly',
                'amount' => 29.99,
                'family_members_included' => 0,
                'additional_family_member_fee' => 0,
                'benefits' => json_encode([
                    'Gym access',
                    'Locker rental',
                    'Basic fitness assessment',
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Premium Membership',
                'description' => 'Full access with additional amenities',
                'plan_type' => 'individual',
                'billing_frequency' => 'monthly',
                'amount' => 59.99,
                'family_members_included' => 0,
                'additional_family_member_fee' => 10.00,
                'benefits' => json_encode([
                    'Gym access',
                    'Locker rental',
                    'Personal trainer sessions (2/month)',
                    'Group fitness classes',
                    'Sauna access',
                    'Nutrition consultation',
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Family Premium',
                'description' => 'Complete family access package',
                'plan_type' => 'family',
                'billing_frequency' => 'monthly',
                'amount' => 99.99,
                'family_members_included' => 4,
                'additional_family_member_fee' => 15.00,
                'benefits' => json_encode([
                    'Gym access for all family members',
                    'Locker rental for all',
                    'Personal trainer sessions (4/month)',
                    'Group fitness classes',
                    'Sauna access',
                    'Kids club access',
                    'Family nutrition workshops',
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($planData as $data) {
            $plan = SubscriptionPlan::where('organization_id', $organization->id)
                ->where('name', $data['name'])
                ->first();

            if (! $plan) {
                $plan = SubscriptionPlan::create(array_merge($data, [
                    'organization_id' => $organization->id,
                ]));
                $this->command->info('✅ Created subscription plan: '.$data['name']);
            } else {
                $this->command->info('✅ Subscription plan already exists: '.$data['name']);
            }

            $plans->put($data['name'], $plan);
        }

        $this->command->info('✅ Created '.$plans->count().' subscription plans');

        return $plans;
    }

    protected function createMembers(Organization $organization): Collection
    {
        $members = collect();
        $memberData = [
            [
                'first_name' => 'John',
                'last_name' => 'Anderson',
                'email' => 'john.anderson@example.com',
                'phone' => '+1-555-0101',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'address' => '123 Main St',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62701',
                'status' => 'active',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@example.com',
                'phone' => '+1-555-0103',
                'date_of_birth' => '1990-07-22',
                'gender' => 'female',
                'address' => '456 Oak Ave',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62702',
                'status' => 'active',
            ],
        ];

        foreach ($memberData as $data) {
            $member = Member::where('email', $data['email'])
                ->where('organization_id', $organization->id)
                ->first();

            if (! $member) {
                // Generate membership number and barcode
                $membershipNumber = 'MEM'.str_pad($organization->id.rand(1000, 9999), 8, '0', STR_PAD_LEFT);
                $barcodeNumber = 'BC'.str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

                $member = Member::create(array_merge($data, [
                    'organization_id' => $organization->id,
                    'membership_number' => $membershipNumber,
                    'barcode_number' => $barcodeNumber,
                    'join_date' => now()->subMonths(rand(1, 12)),
                    'expiry_date' => now()->addMonths(rand(1, 12)),
                ]));
                $this->command->info('✅ Created member: '.$data['first_name'].' '.$data['last_name'].' ('.$membershipNumber.')');
            } else {
                $this->command->info('✅ Member already exists: '.$data['first_name'].' '.$data['last_name']);
            }

            $members->put($data['email'], $member);
        }

        $this->command->info('✅ Created '.$members->count().' demo members');

        return $members;
    }

    protected function createFamilyMembers(Organization $organization, Collection $members): void
    {
        $familyData = [
            [
                'member_email' => 'john.anderson@example.com',
                'first_name' => 'Emma',
                'last_name' => 'Anderson',
                'relationship' => 'daughter',
                'date_of_birth' => '2012-05-20',
                'gender' => 'female',
            ],
        ];

        foreach ($familyData as $data) {
            if (! isset($members[$data['member_email']])) {
                continue;
            }

            $primaryMemberId = $members[$data['member_email']]->id;

            $existing = FamilyMember::where('primary_member_id', $primaryMemberId)
                ->where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->first();

            if (! $existing) {
                $barcodeNumber = 'BC'.str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

                FamilyMember::create([
                    'organization_id' => $organization->id,
                    'primary_member_id' => $primaryMemberId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'relationship' => $data['relationship'],
                    'date_of_birth' => $data['date_of_birth'],
                    'gender' => $data['gender'],
                    'barcode_number' => $barcodeNumber,
                    'status' => 'active',
                ]);
                $this->command->info('✅ Created family member: '.$data['first_name'].' '.$data['last_name']);
            }
        }

        $this->command->info('✅ Created demo family members');
    }

    protected function createMemberSubscriptions(Organization $organization, Collection $members, Collection $plans): void
    {
        $subscriptionData = [
            [
                'member_email' => 'john.anderson@example.com',
                'plan_name' => 'Premium Membership',
                'status' => 'active',
                'auto_renew' => true,
            ],
            [
                'member_email' => 'maria.garcia@example.com',
                'plan_name' => 'Family Premium',
                'status' => 'active',
                'auto_renew' => true,
            ],
        ];

        foreach ($subscriptionData as $data) {
            if (! isset($members[$data['member_email']]) || ! isset($plans[$data['plan_name']])) {
                continue;
            }

            $subscription = MemberSubscription::where('member_id', $members[$data['member_email']]->id)
                ->where('subscription_plan_id', $plans[$data['plan_name']]->id)
                ->first();

            if (! $subscription) {
                MemberSubscription::create([
                    'organization_id' => $organization->id,
                    'member_id' => $members[$data['member_email']]->id,
                    'subscription_plan_id' => $plans[$data['plan_name']]->id,
                    'start_date' => now()->subMonths(rand(1, 6)),
                    'end_date' => now()->addMonths(rand(1, 12)),
                    'status' => $data['status'],
                    'auto_renew' => $data['auto_renew'],
                    'total_amount' => $plans[$data['plan_name']]->amount * 100, // Convert to cents
                    'paid_amount' => $plans[$data['plan_name']]->amount * 100,
                ]);
                $this->command->info('✅ Created member subscription');
            }
        }

        $this->command->info('✅ Created member subscriptions');
    }

    protected function createMemberFees(Organization $organization, Collection $members): void
    {
        $feeData = [
            [
                'member_email' => 'john.anderson@example.com',
                'fee_type' => 'late_fee',
                'description' => 'Late payment fee',
                'amount' => 25.00,
            ],
            [
                'member_email' => 'maria.garcia@example.com',
                'fee_type' => 'additional_service',
                'description' => 'Personal training session',
                'amount' => 50.00,
            ],
        ];

        foreach ($feeData as $data) {
            if (! isset($members[$data['member_email']])) {
                continue;
            }

            $fee = MemberFee::where('member_id', $members[$data['member_email']]->id)
                ->where('description', $data['description'])
                ->first();

            if (! $fee) {
                MemberFee::create([
                    'organization_id' => $organization->id,
                    'member_id' => $members[$data['member_email']]->id,
                    'fee_type' => $data['fee_type'],
                    'description' => $data['description'],
                    'amount' => $data['amount'] * 100, // Convert to cents
                    'due_date' => now()->addDays(rand(1, 30)),
                    'status' => 'pending',
                ]);
                $this->command->info('✅ Created member fee');
            }
        }

        $this->command->info('✅ Created member fees');
    }

    protected function createMembershipUsers(Organization $organization): void
    {
        $users = [
            [
                'name' => 'Membership Manager',
                'email' => 'membership.manager@demo.com',
                'password' => Hash::make('password'),
                'roles' => [MembershipRoles::MEMBERSHIP_MANAGER],
                'permissions' => [
                    MembershipPermissions::VIEW_MEMBERS,
                    MembershipPermissions::CREATE_MEMBERS,
                    MembershipPermissions::EDIT_MEMBERS,
                    MembershipPermissions::DELETE_MEMBERS,
                    MembershipPermissions::MANAGE_MEMBERS,
                    MembershipPermissions::VIEW_SUBSCRIPTIONS,
                    MembershipPermissions::CREATE_SUBSCRIPTIONS,
                    MembershipPermissions::EDIT_SUBSCRIPTIONS,
                    MembershipPermissions::DELETE_SUBSCRIPTIONS,
                    MembershipPermissions::MANAGE_SUBSCRIPTIONS,
                    MembershipPermissions::VIEW_FEES,
                    MembershipPermissions::CREATE_FEES,
                    MembershipPermissions::EDIT_FEES,
                    MembershipPermissions::DELETE_FEES,
                    MembershipPermissions::MANAGE_FEES,
                    MembershipPermissions::PROCESS_PAYMENTS,
                    MembershipPermissions::WAIVE_FEES,
                    MembershipPermissions::PRINT_CARDS,
                    MembershipPermissions::DESIGN_CARDS,
                    MembershipPermissions::VIEW_REPORTS,
                    MembershipPermissions::GENERATE_REPORTS,
                    MembershipPermissions::VIEW_DASHBOARD,
                ],
            ],
            [
                'name' => 'Membership Staff',
                'email' => 'membership.staff@demo.com',
                'password' => Hash::make('password'),
                'roles' => [MembershipRoles::MEMBERSHIP_CLERK],
                'permissions' => [
                    MembershipPermissions::VIEW_MEMBERS,
                    MembershipPermissions::EDIT_MEMBERS,
                    MembershipPermissions::VIEW_SUBSCRIPTIONS,
                    MembershipPermissions::EDIT_SUBSCRIPTIONS,
                    MembershipPermissions::VIEW_FEES,
                    MembershipPermissions::EDIT_FEES,
                    MembershipPermissions::PROCESS_PAYMENTS,
                    MembershipPermissions::PRINT_CARDS,
                    MembershipPermissions::VIEW_REPORTS,
                    MembershipPermissions::VIEW_DASHBOARD,
                ],
            ],
            [
                'name' => 'Front Desk Staff',
                'email' => 'frontdesk@demo.com',
                'password' => Hash::make('password'),
                'roles' => [MembershipRoles::MEMBERSHIP_VIEWER],
                'permissions' => [
                    MembershipPermissions::VIEW_MEMBERS,
                    MembershipPermissions::VIEW_SUBSCRIPTIONS,
                    MembershipPermissions::VIEW_FEES,
                    MembershipPermissions::VIEW_DASHBOARD,
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();

            if (! $user) {
                $user = User::factory()->create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                ]);
                $this->command->info('✅ Created membership user: '.$userData['name'].' ('.$userData['email'].')');
            } else {
                $this->command->info('✅ Membership user already exists: '.$userData['name'].' ('.$userData['email'].')');
            }

            // Attach to organization with roles
            $user->organizations()->sync([$organization->id], [
                'roles' => json_encode($userData['roles']),
            ]);

            // Grant permissions
            foreach ($userData['permissions'] as $permission) {
                try {
                    $user->givePermissionTo($permission, $organization);
                    $this->command->info('✅ Granted permission: '.$permission);
                } catch (\Exception $e) {
                    $this->command->error('❌ Failed to grant permission "'.$permission.'": '.$e->getMessage());
                }
            }
        }

        $this->command->info('✅ Created '.count($users).' membership users with roles and permissions');
    }
}
