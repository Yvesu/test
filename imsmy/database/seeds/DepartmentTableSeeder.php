<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Department;
class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            [
                'name'          =>  'management',
                'description'   =>  '总经办',
                'active'        =>  '1'
            ],
            [
                'name'          =>  'development',
                'description'   =>  '开发部',
                'active'        =>  '1'
            ],
            [
                'name'          =>  'advertising',
                'description'   =>  '广告部',
                'active'        =>  '1'
            ],
        ];
        foreach($departments as $department){
            Department::create($department);
        }
    }
}
