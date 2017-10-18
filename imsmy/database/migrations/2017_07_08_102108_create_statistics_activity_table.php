<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatisticsActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statistics_activity', function(Blueprint $table)
		{
			$table->increments('id');
			$table->bigInteger('activity_id')->unsigned()->index()->comment('活动id');
			$table->integer('forwarding_times')->nullable()->default(0)->comment('阅读数');
			$table->integer('comment_times')->nullable()->default(0)->comment('评论数');
			$table->integer('work_count')->nullable()->default(0)->comment('作品数');
			$table->integer('like_count')->nullable()->default(0)->comment('点赞总数');
			$table->integer('users_count')->unsigned()->nullable()->default(0)->comment('参与总人数');
			$table->integer('time_add')->unsigned()->nullable()->default(0);
			$table->integer('time_update')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statistics_activity');
	}

}
