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
        Schema::create('screen_slides', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['welcome', 'schedule', 'image', 'video']);
            $table->unsignedInteger('position');
            $table->text('motd')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->index(['position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_slides');
    }
};
