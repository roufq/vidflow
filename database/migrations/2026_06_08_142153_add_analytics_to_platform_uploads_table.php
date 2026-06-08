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
        Schema::table('platform_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0)->after('platform_url');
            $table->unsignedBigInteger('likes')->default(0)->after('views');
            $table->timestamp('last_synced_at')->nullable()->after('likes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_uploads', function (Blueprint $table) {
            $table->dropColumn(['views', 'likes', 'last_synced_at']);
        });
    }
};
