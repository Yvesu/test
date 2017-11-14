<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notification', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index()->comment('发送人');
			$table->bigInteger('notice_user_id')->unsigned()->index()->comment('接收提醒者');
			$table->boolean('type')->comment('"0 : tweet @ ,别人发的动态 被@ ,1 : tweet_like自己发的动态 被点赞,2 : tweet_comment 自己发的动态 被评论,3 : tweet_comment @任何动态在评论中被@,4 : tweet_comment_reply 自己发的评论被回复，5：新增粉丝 6：被送奖杯，7：投诉处理成功，8：资金方面');
			$table->bigInteger('type_id')->unsigned()->index()->comment('相应类型的id');
			$table->string('intro')->nullable()->comment('附带消息');
			$table->boolean('status')->nullable()->default(0)->comment('消息的发送情况，0为未发送，1为已发送');
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
		Schema::drop('notification');
	}

}
