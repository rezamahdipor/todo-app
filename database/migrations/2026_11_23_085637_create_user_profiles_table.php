<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('education', 20)->nullable();
            $table->string('field', 60)->nullable();
            $table->string('grade', 2)->nullable();
            $table->string('referral', 20)->nullable();
            $table->unsignedTinyInteger('organized')->nullable();
            $table->json('goals')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('onboarded_at')->nullable();
        });

        // کاربرهای فعلی مجبور به پر کردن فرم نمی‌شن
        DB::table('users')->update(['onboarded_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarded_at');
        });

        Schema::dropIfExists('user_profiles');
    }
};