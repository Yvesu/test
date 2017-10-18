<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetsPushStatisticsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweets_push_statistics', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('tweet_like_user_id_index')->comment('用户外键');
			$table->integer('tweet_push_date')->unsigned()->index('tweet_like_tweet_id_index')->comment('用户获取记录所在的日期');
			$table->integer('time_add')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tweets_push_statistics');
	}

}
