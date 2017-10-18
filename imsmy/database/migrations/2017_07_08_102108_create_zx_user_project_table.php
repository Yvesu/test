<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('project_user_id')->comment('用户id');
			$table->string('name')->comment('项目名称');
			$table->integer('amount')->default(0)->comment('筹资金额');
			$table->string('contacts')->nullable()->comment('联系人');
			$table->bigInteger('phone')->unsigned()->nullable()->comment('联系方式');
			$table->integer('film_id')->index('project_film_id')->comment('zx_film_menu表中id');
			$table->string('city')->nullable()->comment('工作城市');
			$table->string('cover')->nullable()->comment('封面地址');
			$table->integer('from_time')->comment('开始时间');
			$table->integer('end_time')->comment('结束时间');
			$table->string('video')->nullable()->default('')->comment('视频介绍');
			$table->string('scheme')->nullable()->default('')->comment('方案地址');
			$table->boolean('active')->default(0)->comment('状态，0为待审批，1为筹资成功，2为筹资中，3为后台删除');
			$table->integer('time_add')->nullable();
			$table->integer('time_update')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_project');
	}

}
