<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserIntegralExpendLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_integral_expend_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('user_id')->unsigned()->comment('用户ID');
			$table->integer('num')->unsigned()->default(0)->comment('支出的积分');
			$table->string('intro', 500)->default('')->comment('说明');
			$table->boolean('type')->default(0)->comment('类别  0表可用积分  1表冻结积分');
			$table->integer('time_add')->default(0)->comment('添加时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_integral_expend_log');
	}

}
