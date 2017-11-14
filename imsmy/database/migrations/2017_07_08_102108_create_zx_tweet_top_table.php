<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTweetTopTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_tweet_top', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('tweet_id')->unsigned()->unique('tweet_id')->comment('Tweet表中的id');
			$table->integer('top_expires')->nullable()->comment('置顶的有效期');
			$table->integer('recommend_expires')->nullable()->comment('推荐的有效期');
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
		Schema::drop('zx_tweet_top');
	}

}
