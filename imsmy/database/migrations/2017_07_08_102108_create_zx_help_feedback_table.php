<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxHelpFeedbackTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_help_feedback', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->string('content')->default('')->comment('反馈信息');
			$table->string('connect')->nullable()->default('')->comment('联系方式');
			$table->boolean('active')->default(0)->comment('0待审批，1已通过');
			$table->integer('time_add')->unsigned()->comment('添加的时间');
			$table->integer('time_update')->unsigned()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_help_feedback');
	}

}
