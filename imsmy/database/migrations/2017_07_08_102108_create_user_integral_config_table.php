<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserIntegralConfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_integral_config', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->string('name', 500)->default('')->comment('名称');
			$table->integer('min_num')->unsigned()->default(0)->comment('最少限制(积分 >= )');
			$table->integer('num')->unsigned()->default(0)->comment('积分个数');
			$table->boolean('status')->default(1)->comment('0表失效,1表生效');
			$table->string('intro', 500)->comment('说明');
			$table->integer('time_active_start')->default(0)->comment('配置生效开始时间');
			$table->integer('time_active_end')->default(0)->comment('配置生效结束时间');
			$table->integer('time_add')->default(0)->comment('添加时间');
			$table->integer('time_update')->default(0)->comment('更新时间');
			$table->integer('num_default')->default(0)->comment('默认积分数');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_integral_config');
	}

}
