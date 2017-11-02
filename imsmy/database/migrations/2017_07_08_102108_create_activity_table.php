<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->nullable()->default(0)->comment('用户id');
			$table->boolean('active')->default(0)->comment('状态，0为待审批，1为后台审批通过，2为审批不通过');
			$table->boolean('official')->default(0)->comment('是否为官方发布，0为不是，1为是');
			$table->integer('bonus')->unsigned()->default(0)->comment('奖金');
			$table->string('comment')->nullable()->comment('内容');
			$table->string('icon')->nullable()->comment('背景');
			$table->integer('expires')->unsigned()->nullable()->default(0)->comment('赛事有效期');
			$table->boolean('status')->default(0)->comment('是否完成的状态，0为未完成，1为完成，2为失败');
			$table->integer('recommend_expires')->nullable()->default(0)->comment('推荐的有效期');
			$table->integer('time_add')->nullable()->comment('添加时间');
			$table->integer('time_update')->nullable()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity');
	}

}
