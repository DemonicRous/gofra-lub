<?php
// database/migrations/2026_03_30_000006_create_managers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('patronymic')->nullable();
            $table->string('full_name')->nullable(); // временно nullable
            $table->string('short_name')->nullable(); // временно nullable
            $table->string('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('full_name');
            $table->index('last_name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
