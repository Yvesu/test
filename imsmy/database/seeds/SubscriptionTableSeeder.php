<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
class SubscriptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = 10042;
        $users = User::where('id','!=',$id)->get();
        $time = time() - $users->count() * 60;
        $data = [];
        foreach ($users as $key => $user) {
            $data[] = [
                'from'       => $id,
                'to'         => $user->id,
                'created_at' => Carbon::createFromTimestampUTC($time + $key * 60),
                'updated_at' => Carbon::createFromTimestampUTC($time + $key * 60)
            ];
        }
        \DB::table('subscription')->insert($data);
    }
}
