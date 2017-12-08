<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTweetContentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_tweet_content', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('主键id');
			$table->bigInteger('tweet_id')->unsigned()->unique('tweet_content_tweet_id')->comment('动态id');
			$table->string('content', 140)->nullable()->default('')->comment('文字信息，不是转发时，与image、video不能同时为空');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_tweet_content');
	}

}
