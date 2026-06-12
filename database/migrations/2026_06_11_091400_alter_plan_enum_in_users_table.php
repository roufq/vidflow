<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN plan ENUM('free', 'standard', 'pro', 'business') DEFAULT 'free'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            return;
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN plan ENUM('free', 'pro', 'business') DEFAULT 'free'");
    }
};
