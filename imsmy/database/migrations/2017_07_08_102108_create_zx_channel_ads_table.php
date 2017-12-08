<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxChannelAdsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_channel_ads', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('channel_id')->unsigned()->nullable()->default(0)->comment('所属频道的id');
			$table->boolean('active')->nullable()->default(0)->comment('0表示未审批，1表示正常，2表示屏蔽');
			$table->integer('from_time')->nullable()->comment('广告开始时间');
			$table->integer('end_time')->unsigned()->nullable()->default(0)->comment('到期的时间戳');
			$table->boolean('type')->nullable()->default(0)->comment('0为视频，1为图片，2为话题，3为活动，4为网页');
			$table->integer('type_id')->unsigned()->nullable()->default(0)->index('home_channel_id')->comment('所在类型的id');
			$table->string('url', 1000)->nullable()->comment('type为4时的网页地址');
			$table->string('image')->nullable()->default('')->comment('图片URL');
			$table->bigInteger('user_id')->unsigned()->nullable()->index('home_user_id')->comment('上传用户的ID,administrator_b中的id');
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
		Schema::drop('zx_channel_ads');
	}

}
