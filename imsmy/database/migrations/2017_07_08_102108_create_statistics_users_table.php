<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatisticsUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statistics_users', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->nullable()->unique('user_id')->comment('用户id');
			$table->integer('fans_count')->unsigned()->nullable()->default(0)->comment('粉丝数量');
			$table->integer('new_fans_count')->unsigned()->nullable()->default(0)->comment('新粉丝数量');
			$table->integer('follow_count')->unsigned()->nullable()->default(0)->comment('关注总数');
			$table->integer('work_count')->unsigned()->nullable()->default(0)->comment('作品数');
			$table->integer('retweet_count')->unsigned()->nullable()->default(0)->comment('转发总量');
			$table->integer('trophy_count')->unsigned()->nullable()->default(0)->comment('奖杯总量');
			$table->integer('collection_count')->unsigned()->nullable()->default(0)->comment('收藏总量');
			$table->integer('like_count')->unsigned()->nullable()->default(0)->comment('点赞总数');
			$table->integer('topics_count')->nullable()->default(0)->comment('用户拥有的话题总量');
			$table->integer('time_add')->unsigned()->nullable()->default(0)->comment('添加时间');
			$table->integer('time_update')->unsigned()->nullable()->default(0)->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statistics_users');
	}

}
