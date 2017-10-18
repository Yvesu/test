<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectGiftLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project_gift_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('project_id')->unsigned()->index('project_project_id')->comment('投资的项目id');
			$table->bigInteger('user_id')->index('project_user_id')->comment('用户的id');
			$table->integer('gift_id')->index('project_gift_id')->comment('礼物的id');
			$table->boolean('type')->comment('1为基础礼物，2为融资人额外礼物');
			$table->integer('time_add')->unsigned()->nullable();
			$table->integer('time_update')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_project_gift_log');
	}

}
