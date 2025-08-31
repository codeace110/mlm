<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Referral;

class TestUserSeeder extends Seeder
{
    private $firstNames = [
        'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emma', 'James', 'Lisa',
        'Robert', 'Maria', 'William', 'Jennifer', 'Richard', 'Linda', 'Charles',
        'Patricia', 'Daniel', 'Susan', 'Matthew', 'Margaret', 'Anthony', 'Dorothy',
        'Mark', 'Lisa', 'Donald', 'Nancy', 'Steven', 'Karen', 'Paul', 'Betty',
        'Andrew', 'Helen', 'Joshua', 'Sandra', 'Kenneth', 'Donna', 'Kevin', 'Carol',
        'Brian', 'Ruth', 'George', 'Sharon', 'Timothy', 'Michelle', 'Ronald', 'Laura',
        'Jason', 'Sarah', 'Edward', 'Kimberly', 'Jeffrey', 'Deborah', 'Ryan', 'Dorothy',
        'Jacob', 'Amy', 'Nicholas', 'Angela', 'Eric', 'Melissa', 'Jonathan', 'Rebecca',
        'Stephen', 'Virginia', 'Larry', 'Kathleen', 'Justin', 'Pamela', 'Scott', 'Martha',
        'Brandon', 'Debra', 'Benjamin', 'Amanda', 'Samuel', 'Stephanie', 'Gregory', 'Carolyn',
        'Alexander', 'Christine', 'Patrick', 'Janet', 'Sean', 'Catherine', 'Jack', 'Frances',
        'Dennis', 'Ann', 'Jerry', 'Joyce', 'Tyler', 'Diane', 'Aaron', 'Alice', 'Jose', 'Julie'
    ];

    private $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
        'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell',
        'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker',
        'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales', 'Murphy',
        'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper', 'Peterson', 'Bailey',
        'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward', 'Richardson', 'Watson',
        'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes',
        'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster'
    ];

    private $usedNames = [];

    /**
     * Generate a unique realistic full name
     */
    private function generateUniqueName()
    {
        do {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $fullName = $firstName . ' ' . $lastName;
        } while (in_array($fullName, $this->usedNames));

        $this->usedNames[] = $fullName;
        return $fullName;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a test user with account balance
        $testUser = User::create([
            'name' => $this->generateUniqueName(),
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'referral_code' => 'TEST001',
            'sponsor_id' => null, // Root user
            'placement_side' => null,
            'is_admin' => false,
            'status' => 'approved',
            'level' => 0,
            'account_balance' => 5000.00, // Add account balance
            'phone' => '+63 912 345 6789',
            'address' => '123 Test Street, Test City, Philippines',
        ]);

        // Create 8 direct referrals for the test user
        $directReferrals = [];
        for ($i = 1; $i <= 8; $i++) {
            $user = User::create([
                'name' => $this->generateUniqueName(),
                'email' => "direct{$i}@example.com",
                'password' => bcrypt('password'),
                'referral_code' => "DIRECT{$i}00{$i}",
                'sponsor_id' => $testUser->id,
                'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                'is_admin' => false,
                'status' => 'approved',
                'level' => 1,
                'account_balance' => rand(500, 2000), // Random balance
                'phone' => '+63 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'address' => "Address {$i}, City {$i}, Philippines",
            ]);
            $directReferrals[] = $user;

            // Create referral record
            Referral::create([
                'user_id' => $user->id,
                'sponsor_id' => $testUser->id,
                'placement_side' => $user->placement_side,
                'status' => 'approved',
            ]);
        }

        // Create level 2 referrals (referrals of direct referrals)
        foreach ($directReferrals as $index => $sponsor) {
            for ($i = 1; $i <= 4; $i++) {
                $user = User::create([
                    'name' => $this->generateUniqueName(),
                    'email' => "level2_" . (($index * 4) + $i) . "@example.com",
                    'password' => bcrypt('password'),
                    'referral_code' => "LVL2" . (($index * 4) + $i) . "00{$i}",
                    'sponsor_id' => $sponsor->id,
                    'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                    'is_admin' => false,
                    'status' => 'approved',
                    'level' => 2,
                    'account_balance' => rand(200, 1000), // Random balance
                    'phone' => '+63 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                    'address' => "Level2 Address " . (($index * 4) + $i) . ", City, Philippines",
                ]);

                // Create referral record
                Referral::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $sponsor->id,
                    'placement_side' => $user->placement_side,
                    'status' => 'approved',
                ]);
            }
        }

        // Create some pending referrals
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => $this->generateUniqueName(),
                'email' => "pending{$i}@example.com",
                'password' => bcrypt('password'),
                'referral_code' => "PENDING{$i}00{$i}",
                'sponsor_id' => $testUser->id,
                'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                'is_admin' => false,
                'status' => 'pending',
                'level' => 1,
                'account_balance' => 0.00,
                'phone' => '+63 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'address' => "Pending Address {$i}, City, Philippines",
            ]);

            // Create referral record
            Referral::create([
                'user_id' => $user->id,
                'sponsor_id' => $testUser->id,
                'placement_side' => $user->placement_side,
                'status' => 'pending',
            ]);
        }

        $this->command->info('Test user created with email: test@example.com and password: password');
        $this->command->info('Test user has account balance: ₱5,000.00');
        $this->command->info('Created 8 direct referrals, 32 level 2 referrals, and 3 pending referrals');
    }
}