<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing MLM System...\n";

// Test 1: Check if database tables exist
try {
    $binaryTreeColumns = \Schema::getColumnListing('binary_trees');
    echo "✓ Binary Trees table exists with " . count($binaryTreeColumns) . " columns\n";

    $adminCodeColumns = \Schema::getColumnListing('admin_codes');
    echo "✓ Admin Codes table exists with " . count($adminCodeColumns) . " columns\n";
} catch (Exception $e) {
    echo "✗ Database tables missing: " . $e->getMessage() . "\n";
}

// Test 2: Check if models can be instantiated
try {
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ Users exist in database\n";
        echo "  User: " . $user->name . " (ID: " . $user->id . ")\n";
    } else {
        echo "⚠ No users found in database\n";
    }
} catch (Exception $e) {
    echo "✗ User model error: " . $e->getMessage() . "\n";
}

// Test 3: Check if services can be instantiated
try {
    $binaryTreeService = new \App\Services\BinaryTreeService();
    echo "✓ BinaryTreeService instantiated successfully\n";

    $adminCodeService = new \App\Services\AdminCodeService();
    echo "✓ AdminCodeService instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ Service instantiation error: " . $e->getMessage() . "\n";
}

// Test 4: Check if bonus settings exist
try {
    $bonusSettings = \App\Models\BonusSettings::first();
    if ($bonusSettings) {
        echo "✓ Bonus settings found\n";
        echo "  Package value: $" . $bonusSettings->package_value . "\n";
        echo "  Direct bonus: " . $bonusSettings->direct_bonus_percent . "%\n";
    } else {
        echo "⚠ No bonus settings found - will be created automatically\n";
    }
} catch (Exception $e) {
    echo "✗ Bonus settings error: " . $e->getMessage() . "\n";
}

echo "\nSystem analysis complete!\n";