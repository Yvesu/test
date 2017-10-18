<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Position;
use App\Models\Admin\Department;
class PositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $positions = [
            [
                'name'          =>  'manager',
                'description'   =>  '经理',
                'active'        =>  '1'
            ],
            [
                'name'          =>  'employee',
                'description'   =>  '员工',
                'active'        =>  '1'
            ],
            [
                'name'          =>  'trainee',
                'description'   =>  '实习生',
                'active'        =>  '1'
            ],
        ];
        $departments = Department::all();
        foreach($positions as $position){
            foreach($departments as $department){
                $dept_id = ['dept_id' => $department->id];
                Position::create($position + $dept_id);
            }
        }
    }
}
