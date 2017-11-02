<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserTableSeeder::class);
        Model::unguard();

        /**
         * 数据库迁移时进行数据填充
         */
        //添加部门
        //$this->call(DepartmentTableSeeder::class);

        //添加职位
        //$this->call(PositionTableSeeder::class);

        //添加管理员
//        $this->call(AdministratorTableSeeder::class);

        //添加GPUImage
        //$this->call(GPUImageTableSeeder::class);
        //$this->call(GPUImageValueTableSeeder::class);

        //添加订阅
        //$this->call(SubscriptionTableSeeder::class);
        Model::reguard();
    }
}
