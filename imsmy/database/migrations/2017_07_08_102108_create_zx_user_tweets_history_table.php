<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserTweetsHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_tweets_history', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('tweet_id')->unsigned()->nullable()->comment('用户浏览的动态id');
			$table->bigInteger('user_id')->unsigned()->nullable()->comment('用户id');
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
		Schema::drop('zx_user_tweets_history');
	}

}
