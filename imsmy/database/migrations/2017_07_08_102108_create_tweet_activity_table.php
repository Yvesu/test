<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweet_activity', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('activity_id')->unsigned()->index()->comment('活动id');
			$table->bigInteger('tweet_id')->unsigned()->index()->comment('动态id');
			$table->bigInteger('user_id')->unsigned()->comment('动态用户的id');
			$table->bigInteger('like_count')->unsigned()->nullable()->default(0)->comment('参赛期间点赞总数');
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
		Schema::drop('tweet_activity');
	}

}
