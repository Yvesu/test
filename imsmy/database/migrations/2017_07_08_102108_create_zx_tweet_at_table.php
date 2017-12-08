<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTweetAtTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_tweet_at', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('主键');
			$table->bigInteger('tweet_id')->unsigned()->comment('动态id');
			$table->bigInteger('user_id')->unsigned()->comment('被@的用户id');
			$table->string('nickname')->comment('动态中的@的用户昵称');
			$table->integer('time_add')->unsigned()->comment('添加时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_tweet_at');
	}

}
