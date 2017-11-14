<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTweetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tweet', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index()->comment('用户外键');
			$table->bigInteger('retweet')->unsigned()->nullable()->default(0)->index()->comment('转发外键');
			$table->bigInteger('original')->unsigned()->nullable()->default(0)->index()->comment('要转发的原始动态的ID');
			$table->string('photo', 4096)->nullable()->comment('图片字符串数组URL，不是转发时，与content、video不能同时为空');
			$table->string('video')->nullable()->comment('视频URL，不是转发时，与content、image不能同时为空');
			$table->float('duration', 10, 0)->unsigned()->nullable()->default(0)->comment('视频时长');
			$table->boolean('active')->default(0)->comment('0表示未审批，1表示正常，2表示屏蔽,3表自己删除');
			$table->string('location')->nullable()->default('')->comment('用户提交的用位置信息');
			$table->integer('location_id')->nullable()->comment('对应location表中的id');
			$table->integer('nearby_id')->nullable()->comment('对应zx_adcode表中的id');
			$table->boolean('type')->default(0)->comment('动态类型，0 => video , 1 => photo, 2=>text');
			$table->boolean('channel_id')->nullable()->default(0)->comment('所属频道');
			$table->string('screen_shot')->nullable()->comment('视频截图');
			$table->string('shot_width_height')->nullable()->default('0')->comment('截图宽高');
			$table->boolean('user_top')->nullable()->default(0)->comment('用户个人中心的动态置顶，只有两条置顶动态，1为置顶，0为普通');
			$table->boolean('visible')->default(0)->comment('0 : 全部可见，1 : 好友圈可见，2 : 好友圈私密，3 : 仅自己可见，4 : 指定好友可见，5 : 指定好友不可见');
			$table->string('visible_range', 4096)->nullable()->comment('visible为4时，不能为空');
			$table->dateTime('top_expires')->nullable()->comment('置顶的有效期');
			$table->dateTime('recommend_expires')->nullable()->comment('推荐的有效期');
			$table->integer('label_id')->unsigned()->nullable();
			$table->dateTime('label_expires')->nullable();
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
		Schema::drop('tweet');
	}

}
