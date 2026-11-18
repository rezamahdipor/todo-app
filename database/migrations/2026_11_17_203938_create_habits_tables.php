<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('icon', 16)->default('🎯');
            $table->string('color', 7)->default('#8b5cf6');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->date('done_on');
            $table->timestamps();
            $table->unique(['habit_id', 'done_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
        Schema::dropIfExists('habits');
    }
};