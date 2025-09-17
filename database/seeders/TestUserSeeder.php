<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Referral;
use App\Models\BinaryTree;
use App\Models\Earning;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
    private $usedEmails = [];

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
     * Generate a unique email
     */
    private function generateUniqueEmail($base)
    {
        $email = $base . '@example.com';
        if (in_array($email, $this->usedEmails)) {
            $num = 1;
            while (in_array($base . $num . '@example.com', $this->usedEmails)) {
                $num++;
            }
            $email = $base . $num . '@example.com';
        }
        $this->usedEmails[] = $email;
        return $email;
    }

    /**
     * Create a user with referral record
     */
    private function createUser($sponsorId, $placementSide, $level, $namePrefix)
    {
        $user = User::create([
            'id' => 'AKEN' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 6)),
            'name' => $this->generateUniqueName(),
            'email' => $this->generateUniqueEmail($namePrefix . $level),
            'password' => Hash::make('password'),
            'referral_code' => strtoupper(substr($namePrefix, 0, 4)) . $level . rand(100, 999),
            'sponsor_id' => $sponsorId,
            'placement_side' => $placementSide,
            'is_admin' => false,
            'status' => 'approved',
            'level' => $level,
            'balancing_mode' => '1:1',
            'account_balance' => rand(100, 1000) / 100,
            'phone' => '+63 9' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
            'address' => "Address " . rand(1, 100) . ", City, Philippines",
        ]);

        // Create referral record
        Referral::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsorId,
            'placement_side' => $placementSide,
            'status' => 'approved',
        ]);

        return $user;
    }

    /**
     * Recursively build binary tree
     */
    // Removed buildBinaryTree method as we're now using service for placement

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate related tables first to avoid FK errors
        Earning::truncate();
        Withdrawal::truncate();
        Referral::truncate();
        BinaryTree::truncate();
        User::whereNotIn('email', ['test@example.com', 'admin@example.com'])->delete();

        // Create root test user
        $testUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'id' => 'AKENROOT',
                'name' => 'Test Root User',
                'password' => Hash::make('password'),
                'referral_code' => 'ROOT001',
                'sponsor_id' => null,
                'placement_side' => null,
                'is_admin' => false,
                'status' => 'approved',
                'level' => 0,
                'balancing_mode' => '1:1',
                'account_balance' => 5000.00,
                'phone' => '+63 912 345 6789',
                'address' => '123 Test Street, Test City, Philippines',
            ]
        );

        // Create admin
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => 'AKENADMIN',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'referral_code' => 'ADMIN001',
                'sponsor_id' => null,
                'placement_side' => null,
                'is_admin' => true,
                'status' => 'approved',
                'level' => 0,
                'balancing_mode' => '1:1',
                'account_balance' => 0.00,
                'phone' => '+63 912 345 6789',
                'address' => 'Admin Address, Admin City, Philippines',
            ]
        );

        // Create 5 direct referrals for the test user using the actual placement logic
        $binaryTreeService = new \App\Services\BinaryTreeService();
        $directUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $directName = "Direct Referral " . $i;
            $directEmail = "direct$i@example.com";
            $directUser = User::create([
                'id' => 'AKENDIR' . $i,
                'name' => $directName,
                'email' => $directEmail,
                'password' => Hash::make('password'),
                'referral_code' => 'DIR' . $i . rand(100, 999),
                'sponsor_id' => $testUser->id,
                'is_admin' => false,
                'status' => 'approved',
                'level' => 1,
                'balancing_mode' => '1:1',
                'account_balance' => rand(100, 500) / 100,
                'phone' => '+63 9' . rand(10, 99) . rand(1000000, 9999999),
                'address' => "Direct Address $i, City, Philippines",
            ]);

            // Place in binary tree using service (this will handle spillover)
            $binaryTreeService->placeUserInTree($directUser, $testUser);

            $directUsers[] = $directUser;
        }

        $this->command->info('Created 5 direct referrals for Test Root User');
        $this->command->info('Direct users:');
        foreach ($directUsers as $user) {
            $this->command->info("  - {$user->name} ({$user->email}) / password");
        }
        $this->command->info('Test user: test@example.com / password');
        $this->command->info('Admin user: admin@example.com / password');
        $this->command->info('View network at /dashboard/network to see scaling with 5 direct referrals (use level selector)');
    }
}