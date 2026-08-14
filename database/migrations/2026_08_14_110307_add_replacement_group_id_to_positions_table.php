<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('replacement_group_id')
                ->nullable()
                ->after('department_id')
                ->constrained('replacement_groups')
                ->nullOnDelete();
        });

        $this->backfillReplacementGroups();
    }

    /**
     * Backfill one replacement group per existing position.
     *
     * Default: a group named after the position itself.
     * Exception: all positions in "Farmasi & Keuangan" share the "Farmasi" group.
     */
    private function backfillReplacementGroups(): void
    {
        $farmasiDepartmentId = DB::table('departments')
            ->where('name', 'Farmasi & Keuangan')
            ->value('id');

        DB::table('positions')
            ->whereNull('deleted_at')
            ->whereNull('replacement_group_id')
            ->orderBy('id')
            ->get()
            ->each(function ($position) use ($farmasiDepartmentId) {
                $groupName = $position->department_id === $farmasiDepartmentId
                    ? 'Farmasi'
                    : $position->name;

                $groupId = DB::table('replacement_groups')
                    ->where('name', $groupName)
                    ->value('id');

                if (! $groupId) {
                    $groupId = DB::table('replacement_groups')->insertGetId([
                        'name' => $groupName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('positions')
                    ->where('id', $position->id)
                    ->update(['replacement_group_id' => $groupId]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['replacement_group_id']);
            $table->dropColumn('replacement_group_id');
        });
    }
};
