<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCloudStorageShareTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cloud_storage_share', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id_from')->unsigned()->index('share_user_id_from')->comment('分享资源的用户id');
			$table->bigInteger('user_id_to')->unsigned()->index('share_user_id_to')->comment('接收分享的用户id');
			$table->bigInteger('dirname_id')->unsigned()->index('share_dirname_id')->comment('共享目录的id');
			$table->integer('time_add')->nullable()->comment('上传时间');
			$table->integer('time_update')->nullable()->comment('资源的修改时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_cloud_storage_share');
	}

}
