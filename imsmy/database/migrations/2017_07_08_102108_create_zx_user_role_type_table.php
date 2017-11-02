<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserRoleTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_role_type', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->nullable()->default('')->comment('角色类型');
			$table->integer('sort')->unsigned()->nullable()->default(0)->comment('排序');
			$table->boolean('active')->default(0)->comment('状态,0待审批，1审批通过');
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
		Schema::drop('zx_user_role_type');
	}

}
