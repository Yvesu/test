<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxActivityRecommendTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_activity_recommend', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('activity_id')->unsigned()->nullable()->default(0)->index('home_activity_id')->comment('活动id');
			$table->string('image')->nullable()->default('')->comment('图片URL');
			$table->integer('recommend_expires')->unsigned()->nullable()->default(0)->comment('到期的时间戳');
			$table->boolean('active')->nullable()->default(0)->comment('0表示未审批，1表示正常，2表示屏蔽');
			$table->bigInteger('user_id')->unsigned()->nullable()->index('home_user_id')->comment('上传用户的ID');
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
		Schema::drop('zx_activity_recommend');
	}

}
