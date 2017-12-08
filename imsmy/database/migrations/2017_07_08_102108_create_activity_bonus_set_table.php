<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityBonusSetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_bonus_set', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->boolean('level')->default(4)->comment('奖金的级别，1为第一级，2为第二级，3为第三级，4为全部');
			$table->integer('count_user')->unsigned()->default(0)->comment('对应级别的用户数量');
			$table->float('amount', 10)->unsigned()->default(0.00)->comment('赛事每一级对应的分成比例，单位1');
			$table->float('prorata', 10)->unsigned()->default(0.20)->comment('向关注者分成比例，单位1');
			$table->boolean('active')->default(1)->comment('状态，1正常，0删除');
			$table->integer('time_add')->unsigned()->comment('添加时间');
			$table->integer('time_update')->unsigned()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_bonus_set');
	}

}
