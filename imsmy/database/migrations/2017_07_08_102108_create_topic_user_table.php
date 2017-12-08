<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTopicUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('topic_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('tweet_topic_user_id_index');
			$table->bigInteger('topic_id')->unsigned()->index('tweet_topic_topic_id_index');
			$table->boolean('status')->nullable()->default(1)->comment('参与话题的状态，1为参与，0为删除话题动态默认退出所参与的话题');
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
		Schema::drop('topic_user');
	}

}
