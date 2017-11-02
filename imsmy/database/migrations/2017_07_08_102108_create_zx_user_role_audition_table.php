<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserRoleAuditionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_role_audition', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('role_id')->unsigned()->default(0)->index('role_role_id')->comment('zx_user_role 表 id');
			$table->string('content')->nullable()->comment('角色试镜要求');
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
		Schema::drop('zx_user_role_audition');
	}

}
