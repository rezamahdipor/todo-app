<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10);
            $table->unsignedBigInteger('target_id');
            $table->string('reason', 20);
            $table->string('note', 300)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'target_id']);
            $table->index(['resolved_at', 'type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};