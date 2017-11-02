<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_role', function(Blueprint $table)
		{
			$table->increments('id');
			$table->bigInteger('user_id')->unsigned()->default(0)->index('role_user_id')->comment('发布用户id');
			$table->string('title')->nullable()->comment('剧名');
			$table->string('director', 50)->nullable()->comment('导演');
			$table->integer('film_id')->unsigned()->default(0)->index('role_film_id')->comment('zx_film_menu表 id');
			$table->integer('time_from')->unsigned()->comment('开机时间');
			$table->integer('time_end')->unsigned()->comment('截止时间');
			$table->string('period', 100)->nullable()->comment('拍摄周期');
			$table->string('site')->nullable()->comment('拍摄地点');
			$table->string('cover')->nullable()->comment('剧照');
			$table->boolean('active')->default(0)->comment('状态,0待审批，1审批通过，2审批未通过或过期删除');
			$table->integer('time_add')->unsigned()->nullable()->comment('上传时间');
			$table->integer('time_update')->unsigned()->nullable()->comment('修改时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_role');
	}

}
