<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatisticsTweetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statistics_tweet', function(Blueprint $table)
		{
			$table->increments('id');
			$table->bigInteger('tweet_id')->unsigned()->unique('statistics_tweet_tweet_id_index')->comment('动态id');
			$table->bigInteger('user_id')->unsigned()->index()->comment('用户id');
			$table->integer('browse_times')->unsigned()->nullable()->default(0)->comment('观看次数');
			$table->integer('like_count')->nullable()->default(0)->comment('点赞总数');
			$table->integer('reply_count')->nullable()->default(0)->comment('评论总数');
			$table->integer('retweet_count')->unsigned()->nullable()->default(0)->comment('转发总量');
			$table->float('tweet_grade_total', 10, 0)->unsigned()->nullable()->default(0)->comment('动态评分总数');
			$table->integer('tweet_grade_times')->unsigned()->nullable()->default(0)->comment('评分次数');
			$table->integer('time_add')->unsigned()->nullable()->default(0);
			$table->integer('time_update')->unsigned()->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statistics_tweet');
	}

}
