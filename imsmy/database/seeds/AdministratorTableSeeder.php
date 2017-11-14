<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\Administrator;
class AdministratorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = [
            'email'                             =>      'admin@goobird.com',
            'password'                          =>      '$2y$10$N4/ohp1aiFLmVsGK2X9mhu7/2eJZqTImV.u.cOqzrGqGXj1ILGdrq',
            'avatar'                            =>      'default/default.png',
            'sex'                               =>      '1',
            'position_id'                       =>      '1',
            'ID_card_URl'                       =>      'default/default.png',
            'phone'                             =>      '13888888888',
            'name'                              =>      '华哥',
            'secondary_contact_name'            =>      '第二联系人',
            'secondary_contact_phone'           =>      '13999999999',
            'secondary_contact_relationship'    =>      'brother'

        ];
        Administrator::create($admin);
    }
}
