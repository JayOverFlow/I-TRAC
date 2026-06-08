<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppParent;
use App\Models\AppItem;
use App\Models\User;
use App\Models\Department;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Locate the Dean - College of Education and Sciences user dynamically
        $user = User::whereHas('roles', function ($query) {
            $query->where('role_name', 'Dean - College of Education and Sciences');
        })->first();

        // Fallback to user ID 5 if role query returns null
        if (!$user) {
            $user = User::find(5);
        }

        // Fallback to first available user if user 5 is not found
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->command->error("No users found to assign the APP. Please seed users first.");
            return;
        }

        // 2. Locate department College of Education and Sciences (dep_id = 1)
        $department = Department::where('dep_name', 'College of Education and Sciences')
            ->orWhere('dep_acronym', 'CES')
            ->first();

        $depId = $department ? $department->dep_id : 1;

        // 3. Prepare the unique code and title matching the logic in CreateAppController
        $year = '2026';
        $appCount = AppParent::where('app_dep_id_fk', $depId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        $appUniqueCode = 'APP-' . $year . '-' . str_pad($appCount, 2, '0', STR_PAD_LEFT);
        $appTitle = "Annual Procurement Plan for Fiscal Year " . $year;

        if ($appCount > 1) {
            $appTitle .= " Version " . $appCount;
        }

        // 4. Create the AppParent record (Header)
        $app = AppParent::create([
            'app_title'           => $appTitle,
            'saved_by_user_id_fk' => $user->user_id,
            'app_dep_id_fk'       => $depId,
            'app_unique_code'     => $appUniqueCode,
            'app_status'          => 'Done',
            'app_total'           => 0, // Will update with sum of items
        ]);

        // 5. Define exactly 5 associated AppItems with realistic data satisfying controller constraints
        $items = [
            [
                'app_item_proj_title'    => 'Office Supplies for Academic Staff',
                'app_items_end_user'     => 'CES Academic Staff',
                'app_items_gen_desc'     => 'Various office papers, pens, and binders',
                'app_items_mode'         => 'Shopping',
                'app_items_criteria'     => 'Lowest price',
                'app_items_covered'      => 'Yes',
                'app_items_start'        => '2026-03-01 08:00:00',
                'app_items_end'          => '2026-03-15 17:00:00',
                'app_items_source'       => 'Fid. Fund',
                'app_items_esti_budget'  => 25000,
                'app_items_tools'        => 'Office administrative tools',
                'app_items_remarks'      => 'Standard quarterly replenishment',
            ],
            [
                'app_item_proj_title'    => 'Science Laboratory Equipment Upgrade',
                'app_items_end_user'     => 'Chemistry and Physics Lab',
                'app_items_gen_desc'     => 'Microscopes and calibration kits',
                'app_items_mode'         => 'Public Bidding',
                'app_items_criteria'     => 'Quality and technical specifications',
                'app_items_covered'      => 'No',
                'app_items_start'        => '2026-04-10 09:00:00',
                'app_items_end'          => '2026-05-10 17:00:00',
                'app_items_source'       => 'GAA',
                'app_items_esti_budget'  => 350000,
                'app_items_tools'        => 'Laboratory instructional tools',
                'app_items_remarks'      => 'Required for accreditation',
            ],
            [
                'app_item_proj_title'    => 'Reference Books and E-Journals',
                'app_items_end_user'     => 'College Library Department',
                'app_items_gen_desc'     => 'Educational reference books and e-journal subs',
                'app_items_mode'         => 'Direct Contracting',
                'app_items_criteria'     => 'Sole distributor validation',
                'app_items_covered'      => 'No',
                'app_items_start'        => '2026-06-01 08:00:00',
                'app_items_end'          => '2026-06-30 17:00:00',
                'app_items_source'       => 'Special Trust Fund',
                'app_items_esti_budget'  => 120000,
                'app_items_tools'        => 'Research and reference tools',
                'app_items_remarks'      => 'Annual subscription renewal',
            ],
            [
                'app_item_proj_title'    => 'Ergonomic Chairs for Faculty Rooms',
                'app_items_end_user'     => 'CES Faculty Members',
                'app_items_gen_desc'     => 'High-back ergonomic office chairs',
                'app_items_mode'         => 'Shopping',
                'app_items_criteria'     => 'Lowest compliant quotation',
                'app_items_covered'      => 'Yes',
                'app_items_start'        => '2026-07-01 08:00:00',
                'app_items_end'          => '2026-07-20 17:00:00',
                'app_items_source'       => 'Income',
                'app_items_esti_budget'  => 65000,
                'app_items_tools'        => 'Faculty office furniture',
                'app_items_remarks'      => 'Replacement of damaged chairs',
            ],
            [
                'app_item_proj_title'    => 'Multimedia Projectors for Classrooms',
                'app_items_end_user'     => 'CES Lecture Classrooms',
                'app_items_gen_desc'     => 'Full HD smart projectors with mounts',
                'app_items_mode'         => 'Negotiated Procurement',
                'app_items_criteria'     => 'Technical compliance and cost',
                'app_items_covered'      => 'Yes',
                'app_items_start'        => '2026-08-15 08:00:00',
                'app_items_end'          => '2026-09-05 17:00:00',
                'app_items_source'       => 'GAA',
                'app_items_esti_budget'  => 180000,
                'app_items_tools'        => 'Multimedia teaching tools',
                'app_items_remarks'      => 'Smart classroom initiative phase 2',
            ],
        ];

        $totalEstiBudget = 0;
        foreach ($items as $itemData) {
            $estiBudget = (float)($itemData['app_items_esti_budget'] ?? 0);
            $totalEstiBudget += $estiBudget;

            AppItem::create(array_merge($itemData, [
                'app_id_fk' => $app->app_id,
            ]));
        }

        // 6. Update APP header total budget with the sum of items
        $app->update([
            'app_total' => $totalEstiBudget,
        ]);

        $this->command->info("Successfully seeded 1 Annual Procurement Plan with 5 items under {$user->user_fullname} (Dean - College of Education and Sciences).");
    }
}
