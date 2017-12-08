<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserGoldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_golds', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('user_id')->unsigned()->unique('user_id')->comment('用户ID');
			$table->integer('total')->unsigned()->default(0)->comment('总金币');
			$table->integer('t_use')->unsigned()->default(0)->comment('可用金币');
			$table->integer('t_used')->unsigned()->default(0)->comment('已用金币');
			$table->integer('t_freeze')->unsigned()->default(0)->comment('冻结的金币');
			$table->integer('time_add')->default(0)->comment('添加时间');
			$table->integer('time_update')->default(0)->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_golds');
	}

}
