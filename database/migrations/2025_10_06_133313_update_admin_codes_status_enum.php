<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the old constraint first
        DB::statement('ALTER TABLE admin_codes DROP CONSTRAINT IF EXISTS check_admin_code_status');

        // Change the status enum to include all statuses used by EnhancedReferralCodeService
        DB::statement("ALTER TABLE admin_codes MODIFY COLUMN status ENUM('available', 'assigned', 'used', 'expired') DEFAULT 'available'");

        // Add the new constraint that matches the enum
        DB::statement('ALTER TABLE admin_codes ADD CONSTRAINT check_admin_code_status CHECK (status IN ("available", "assigned", "used", "expired"))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the new constraint
        DB::statement('ALTER TABLE admin_codes DROP CONSTRAINT IF EXISTS check_admin_code_status');

        // Revert back to the original enum
        DB::statement("ALTER TABLE admin_codes MODIFY COLUMN status ENUM('issued', 'unused', 'used') DEFAULT 'issued'");

        // Add back the old constraint
        DB::statement('ALTER TABLE admin_codes ADD CONSTRAINT check_admin_code_status CHECK (status IN ("issued", "unused", "used", "expired"))');
    }
};
