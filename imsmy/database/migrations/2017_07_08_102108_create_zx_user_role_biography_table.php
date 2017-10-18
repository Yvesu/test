<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserRoleBiographyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_role_biography', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('details_id')->unsigned()->default(0)->index('role_details_id')->comment('zx_user_details 表 id');
			$table->string('intro')->nullable()->comment('角色小传');
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
		Schema::drop('zx_user_role_biography');
	}

}
