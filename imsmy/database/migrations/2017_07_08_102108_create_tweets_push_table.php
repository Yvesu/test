<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetsPushTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweets_push', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('tweet_id')->default(0)->unique('tweet_push_tweet_id')->comment('动态的字符串');
			$table->integer('date')->unsigned()->default(0)->index('tweet_push_date')->comment('当天日期Ymd');
			$table->boolean('active')->default(0)->comment('0表示未审批，1表示正常，2表示屏蔽');
			$table->integer('time_add')->nullable();
			$table->integer('time_update')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tweets_push');
	}

}
