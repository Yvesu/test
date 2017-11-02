<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserRoleIntroTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_role_intro', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('role_id')->unsigned()->default(0)->index('role_role_id')->comment('zx_user_role 表 id');
			$table->text('intro', 65535)->nullable()->comment('剧情简介');
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
		Schema::drop('zx_user_role_intro');
	}

}
