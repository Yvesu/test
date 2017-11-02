<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserComplainsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_complains', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cause_id')->unsigned()->nullable()->index('complains_cause_id')->comment('举报原因的id');
			$table->bigInteger('user_id')->unsigned()->index('complains_user_id')->comment('用户id');
			$table->boolean('status')->default(0)->comment('处理状态，0未受理，1为正常，2为屏蔽');
			$table->boolean('type')->nullable()->default(0)->comment('类型，0动态1话题2活动3评论');
			$table->bigInteger('type_id')->unsigned()->default(0)->index('complains_type_id')->comment('所在类型的id');
			$table->text('content', 65535)->nullable()->comment('举报内容');
			$table->integer('staff_id')->unsigned()->nullable()->comment('处理该投诉的工作人员的id');
			$table->integer('time_add')->unsigned()->nullable()->comment('添加时间');
			$table->integer('time_update')->unsigned()->nullable()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_complains');
	}

}
