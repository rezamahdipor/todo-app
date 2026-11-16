<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('priority');
        });

        // ترتیب فعلی (جدیدترین بالا) رو برای هر کاربر ثبت می‌کنیم
        $counters = [];
        $tasks = DB::table('tasks')->orderBy('user_id')->orderByDesc('id')->get(['id', 'user_id']);

        foreach ($tasks as $task) {
            $counters[$task->user_id] = ($counters[$task->user_id] ?? 0) + 1;

            DB::table('tasks')
                ->where('id', $task->id)
                ->update(['position' => $counters[$task->user_id]]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};