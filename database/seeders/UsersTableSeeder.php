<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Referral;

class UsersTableSeeder extends Seeder
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
        // Create root user (admin)
        $rootUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@mlm.com',
            'password' => bcrypt('password'),
            'referral_code' => 'ADMIN001',
            'is_admin' => true,
            'status' => 'approved',
            'level' => 0,
            'account_balance' => 50000.00, // Admin has plenty of balance
            'phone' => '+639123456789',
            'address' => '123 Admin Street, Admin City, Philippines',
        ]);

        // Create level 1 users (direct referrals of admin)
        $level1Users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => $this->generateUniqueName(),
                'email' => "level1_user{$i}@mlm.com",
                'password' => bcrypt('password'),
                'referral_code' => "LVL1{$i}00{$i}",
                'sponsor_id' => $rootUser->id,
                'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                'is_admin' => false,
                'status' => 'approved',
                'level' => 1,
                'account_balance' => rand(5000, 25000), // Random balance between 5k-25k
                'phone' => '+63' . rand(900000000, 999999999),
                'address' => rand(100, 999) . ' ' . ['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr'][array_rand(['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr'])] . ', ' .
                           ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig'][array_rand(['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig'])] . ', Philippines',
            ]);
            $level1Users[] = $user;

            // Create referral record
            Referral::create([
                'user_id' => $user->id,
                'sponsor_id' => $rootUser->id,
                'placement_side' => $user->placement_side,
                'status' => 'approved',
            ]);
        }

        // Create level 2 users (referrals of level 1 users)
        $level2Users = [];
        foreach ($level1Users as $index => $sponsor) {
            for ($i = 1; $i <= 3; $i++) {
                $user = User::create([
                    'name' => $this->generateUniqueName(),
                    'email' => "level2_user" . (($index * 3) + $i) . "@mlm.com",
                    'password' => bcrypt('password'),
                    'referral_code' => "LVL2" . (($index * 3) + $i) . "00" . $i,
                    'sponsor_id' => $sponsor->id,
                    'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                    'is_admin' => false,
                    'status' => 'approved',
                    'level' => 2,
                    'account_balance' => rand(2000, 15000), // Random balance between 2k-15k
                    'phone' => '+63' . rand(900000000, 999999999),
                    'address' => rand(100, 999) . ' ' . ['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr'][array_rand(['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr'])] . ', ' .
                               ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Cebu', 'Davao'][array_rand(['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Cebu', 'Davao'])] . ', Philippines',
                ]);
                $level2Users[] = $user;

                // Create referral record
                Referral::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $sponsor->id,
                    'placement_side' => $user->placement_side,
                    'status' => 'approved',
                ]);
            }
        }

        // Create level 3 users (referrals of level 2 users)
        foreach ($level2Users as $index => $sponsor) {
            for ($i = 1; $i <= 2; $i++) {
                $user = User::create([
                    'name' => $this->generateUniqueName(),
                    'email' => "level3_user" . (($index * 2) + $i) . "@mlm.com",
                    'password' => bcrypt('password'),
                    'referral_code' => "LVL3" . (($index * 2) + $i) . "00" . $i,
                    'sponsor_id' => $sponsor->id,
                    'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                    'is_admin' => false,
                    'status' => 'approved',
                    'level' => 3,
                    'account_balance' => rand(1000, 8000), // Random balance between 1k-8k
                    'phone' => '+63' . rand(900000000, 999999999),
                    'address' => rand(100, 999) . ' ' . ['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr', 'Cedar Ln', 'Birch St'][array_rand(['Main St', 'Oak Ave', 'Pine Rd', 'Elm Blvd', 'Maple Dr', 'Cedar Ln', 'Birch St'])] . ', ' .
                               ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Cebu', 'Davao', 'Baguio', 'Iloilo'][array_rand(['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Cebu', 'Davao', 'Baguio', 'Iloilo'])] . ', Philippines',
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

        // Create some pending users
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => $this->generateUniqueName(),
                'email' => "pending_user{$i}@mlm.com",
                'password' => bcrypt('password'),
                'referral_code' => "PEND{$i}00{$i}",
                'sponsor_id' => $level1Users[0]->id,
                'placement_side' => $i % 2 == 0 ? 'left' : 'right',
                'is_admin' => false,
                'status' => 'pending',
                'level' => 2, // Pending users under level 1 sponsor
                'account_balance' => rand(500, 2000), // Small balance for pending users
                'phone' => '+63' . rand(900000000, 999999999),
                'address' => rand(100, 999) . ' ' . ['Main St', 'Oak Ave', 'Pine Rd'][array_rand(['Main St', 'Oak Ave', 'Pine Rd'])] . ', ' .
                           ['Manila', 'Quezon City', 'Makati'][array_rand(['Manila', 'Quezon City', 'Makati'])] . ', Philippines',
            ]);

            // Create referral record
            Referral::create([
                'user_id' => $user->id,
                'sponsor_id' => $level1Users[0]->id,
                'placement_side' => $user->placement_side,
                'status' => 'pending',
            ]);
        }
    }
}
