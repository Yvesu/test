<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetTrophyLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweet_trophy_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('from')->unsigned()->comment('赠送奖杯的用户ID');
			$table->integer('to')->unsigned()->nullable()->comment('接收奖杯者的id');
			$table->integer('tweet_id')->unsigned()->comment('动态ID');
			$table->integer('trophy_id')->unsigned()->comment('奖杯ID');
			$table->integer('num')->default(1)->comment('获得的奖杯数量');
			$table->boolean('anonymity')->nullable()->default(0)->comment('0为公开，1为匿名颁奖');
			$table->integer('date')->unsigned()->comment('颁奖日期');
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
		Schema::drop('tweet_trophy_log');
	}

}
