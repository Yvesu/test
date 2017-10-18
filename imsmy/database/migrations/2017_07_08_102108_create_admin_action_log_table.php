<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminActionLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_action_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('aid')->unsigned()->comment('管理员id');
			$table->string('tid', 100)->comment('处理的动态id');
			$table->boolean('time')->comment('动作的时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_action_log');
	}

}
