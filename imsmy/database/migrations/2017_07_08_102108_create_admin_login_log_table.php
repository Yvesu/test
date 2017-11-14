<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminLoginLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_login_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('aid')->unsigned()->comment('管理员id');
			$table->string('ip', 100)->comment('登陆IP');
			$table->integer('time')->comment('登陆时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_login_log');
	}

}
