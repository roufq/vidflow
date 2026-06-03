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
        Schema::create('platform_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_id')->constrained('upload_jobs')->cascadeOnDelete();
            $table->enum('platform', ['youtube', 'facebook', 'instagram', 'tiktok']);
            $table->enum('status', ['pending', 'uploading', 'done', 'failed'])->default('pending');
            $table->integer('progress_percent')->default(0);
            $table->string('platform_video_id')->nullable();
            $table->text('platform_url')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_uploads');
    }
};
