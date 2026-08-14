<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\ReplacementGroup;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'Manajemen' => [
                'Direktur',
                'Manajer',
                'Casemix HRD',
                'IT',
                'Staff Laboratorium',
                'Keuangan',
            ],
            'Pelayanan Medis' => [
                'Perawat',
                'Bidan',
                'Rekam Medis',
                'Dokter',
            ],
            'Security & Driver' => [
                'Security',
            ],
            'Cleaning Service' => [
                'Cleaning Service',
            ],
            'Farmasi & Keuangan' => [
                'Apoteker',
                'Asisten Apoteker',
            ],
        ];

        foreach ($mapping as $deptName => $positions) {
            $department = Department::where('name', $deptName)->first();

            if ($department) {
                foreach ($positions as $posName) {
                    $position = Position::updateOrCreate(
                        ['name' => $posName],
                        ['department_id' => $department->id]
                    );

                    // Assign replacement group when not already configured:
                    // "Farmasi & Keuangan" positions share the "Farmasi" group,
                    // every other position defaults to a group named after itself.
                    if ($position->replacement_group_id === null) {
                        $groupName = $deptName === 'Farmasi & Keuangan' ? 'Farmasi' : $posName;
                        $group = ReplacementGroup::firstOrCreate(['name' => $groupName]);

                        $position->update(['replacement_group_id' => $group->id]);
                    }
                }
            }
        }
    }
}
