<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCloudStorageSpaceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cloud_storage_space', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('space_user_id')->comment('用户id');
			$table->bigInteger('total_space')->unsigned()->default(10737418240)->comment('默认10G');
			$table->bigInteger('used_space')->unsigned()->default(0)->comment('已用空间');
			$table->bigInteger('free_space')->unsigned()->default(10737418240)->comment('剩余空间，默认10G，单位b');
			$table->integer('time_from')->unsigned()->comment('有效期开始日期');
			$table->integer('time_end')->unsigned()->comment('有效期结束日期');
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
		Schema::drop('zx_cloud_storage_space');
	}

}
