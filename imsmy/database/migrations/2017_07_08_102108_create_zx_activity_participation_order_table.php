<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxActivityParticipationOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_activity_participation_order', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->comment('参与赛事的用户id');
			$table->bigInteger('activity_id')->unsigned()->comment('赛事表activity中的id');
			$table->boolean('level')->default(4)->comment('奖金分配的类型，1为第一级，2为第二级，3为第三级，4为第四级');
			$table->float('bonus', 10, 0)->unsigned()->comment('不同名次的奖金');
			$table->boolean('status')->default(0)->comment('订单的处理状态，0未发放，1发放');
			$table->integer('time_add')->unsigned()->nullable()->comment('添加的时间戳');
			$table->integer('time_update')->unsigned()->nullable()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_activity_participation_order');
	}

}
