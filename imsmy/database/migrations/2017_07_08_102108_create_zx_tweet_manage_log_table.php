<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTweetManageLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_tweet_manage_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('admin_id')->unsigned()->index('verify_admin_id')->comment('后台操作人员的id');
			$table->bigInteger('data_id')->index('verify_verify_id')->comment('tweet 表中的id');
			$table->boolean('active')->nullable()->default(0)->comment('处理类型，0为未设置，1为正常，2为屏蔽');
			$table->string('channel_ids')->nullable()->comment('频道id');
			$table->string('top_expires')->nullable()->comment('置顶的有效期');
			$table->string('recommend_expires')->nullable()->comment('推荐的有效期');
			$table->string('topic_ids')->nullable()->comment('所属话题的id');
			$table->string('activity_ids')->nullable()->comment('所属活动的id');
			$table->boolean('push_date')->nullable()->comment('推送日期');
			$table->boolean('hot')->nullable()->default(0)->comment('是否热门，0为默认未操作，1为热门，2为关闭');
			$table->integer('time_add')->nullable()->comment('操作时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_tweet_manage_log');
	}

}
