<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTweetExamineAmountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_tweet_examine_amount', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('admin_id')->unsigned()->index('verify_admin_id')->comment('后台操作人员的id');
			$table->integer('amount')->default(0)->index('verify_verify_id')->comment('视频审批数量统计');
			$table->integer('date')->nullable()->default(0)->comment('审批年月日');
			$table->integer('time_add')->nullable()->comment('添加时间');
			$table->integer('time_update')->nullable()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_tweet_examine_amount');
	}

}
