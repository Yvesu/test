<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetReplyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweet_reply', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index()->comment('用户外键');
			$table->bigInteger('tweet_id')->unsigned()->index()->comment('Tweet外键');
			$table->bigInteger('reply_id')->unsigned()->nullable()->index()->comment('上一级评论的id，无则为空');
			$table->integer('reply_user_id')->nullable()->comment('上一级评论的user_id');
			$table->integer('barrage')->nullable()->comment('弹幕');
			$table->string('content')->comment('评论内容');
			$table->integer('like_count')->nullable()->default(0)->comment('点赞总数');
			$table->float('grade', 10, 0)->unsigned()->nullable()->default(0)->comment('用户对动态的评分');
			$table->boolean('anonymity')->nullable()->default(0)->comment('0为公开，1为匿名评论');
			$table->boolean('status')->nullable()->default(0)->comment('0为正常，1为后台屏蔽，2为自己删除');
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
		Schema::drop('tweet_reply');
	}

}
