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
        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropForeign(['recurring_assignment_id']);
            $table->dropColumn(['recurring_assignment_id', 'is_detached']);
        });

        Schema::dropIfExists('recurring_assignments');
    }

    public function down(): void
    {
        Schema::create('recurring_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->string('period');
            $table->string('start_week');
            $table->string('end_week');
            $table->timestamps();
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->foreignId('recurring_assignment_id')->nullable()->constrained('recurring_assignments')->cascadeOnDelete();
            $table->boolean('is_detached')->default(false);
        });
    }
};
