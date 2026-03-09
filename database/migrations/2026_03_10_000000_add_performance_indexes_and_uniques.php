<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicateAssignmentUsers();
        $this->deduplicateAssignmentAttended();

        Schema::table('assignment_users', function (Blueprint $table): void {
            $table->unique(['assignment_id', 'user_id'], 'assignment_users_assignment_user_unique');
        });

        Schema::table('assignment_attended', function (Blueprint $table): void {
            $table->unique(['assignment_id', 'attended_id'], 'assignment_attended_assignment_attended_unique');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->index(['date', 'region_classification'], 'assignments_date_region_idx');
            $table->index('boarding_date', 'assignments_boarding_date_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'is_active'], 'users_role_active_idx');
        });

        Schema::table('attendeds', function (Blueprint $table): void {
            $table->index('name', 'attendeds_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attendeds', function (Blueprint $table): void {
            $table->dropIndex('attendeds_name_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_active_idx');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropIndex('assignments_date_region_idx');
            $table->dropIndex('assignments_boarding_date_idx');
        });

        Schema::table('assignment_attended', function (Blueprint $table): void {
            $table->dropUnique('assignment_attended_assignment_attended_unique');
        });

        Schema::table('assignment_users', function (Blueprint $table): void {
            $table->dropUnique('assignment_users_assignment_user_unique');
        });
    }

    private function deduplicateAssignmentUsers(): void
    {
        $duplicateGroups = DB::table('assignment_users')
            ->select('assignment_id', 'user_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('assignment_id', 'user_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('assignment_users')
                ->where('assignment_id', $group->assignment_id)
                ->where('user_id', $group->user_id)
                ->where('id', '<>', $group->keep_id)
                ->delete();
        }
    }

    private function deduplicateAssignmentAttended(): void
    {
        $duplicateGroups = DB::table('assignment_attended')
            ->select('assignment_id', 'attended_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('assignment_id', 'attended_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('assignment_attended')
                ->where('assignment_id', $group->assignment_id)
                ->where('attended_id', $group->attended_id)
                ->where('id', '<>', $group->keep_id)
                ->delete();
        }
    }
};
