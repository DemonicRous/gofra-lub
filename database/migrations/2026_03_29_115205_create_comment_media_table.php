<?php
// database/migrations/2026_03_29_000002_create_comment_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('audit_comments')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('audit_media')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['comment_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_media');
    }
};
