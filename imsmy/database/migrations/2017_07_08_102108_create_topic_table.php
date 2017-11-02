<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTopicTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('topic', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('name')->comment('名称');
			$table->boolean('active')->default(0)->comment('状态，0为普通，1为后台审批通过，2为屏蔽');
			$table->boolean('type')->nullable()->default(0)->comment('0为话题，1为活动');
			$table->integer('forwarding_time')->default(0)->comment('阅读数，待删除');
			$table->integer('comment_time')->default(0)->comment('评论数，待删除');
			$table->integer('work_count')->default(0)->comment('作品数，待删除');
			$table->integer('users_count')->unsigned()->nullable()->default(0)->comment('参加话题的用户量，待删除');
			$table->boolean('official')->default(0)->comment('是否为官方发布，0为不是，1为是');
			$table->string('comment')->nullable();
			$table->string('color', 10)->nullable()->comment('主题背景色(新增)');
			$table->string('icon')->nullable();
			$table->string('size', 50)->nullable()->comment('图片尺寸');
			$table->dateTime('recommend_expires')->nullable()->comment('推荐的有效期');
			$table->string('hash_icon')->nullable();
			$table->bigInteger('user_id')->unsigned()->nullable()->comment('发布用户id');
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
		Schema::drop('topic');
	}

}
