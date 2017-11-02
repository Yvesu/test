<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_users', function(Blueprint $table)
		{
			$table->bigInteger('uid', true)->unsigned();
			$table->string('user_id', 2049)->index('part_of_user_id');
			$table->char('sha1_user_id', 128)->unique('sha1_user_id');
			$table->string('user_pw')->nullable()->index('user_pw');
			$table->timestamp('acc_create_time')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('last_login')->index('last_login');
			$table->dateTime('last_logout')->index('last_logout');
			$table->integer('online_status')->nullable()->default(0)->index('online_status');
			$table->integer('failed_logins')->nullable()->default(0);
			$table->integer('account_status')->nullable()->default(1)->index('account_status');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_users');
	}

}
