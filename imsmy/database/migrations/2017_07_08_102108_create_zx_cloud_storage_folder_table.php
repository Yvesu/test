<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCloudStorageFolderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cloud_storage_folder', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('folder_user_id')->comment('用户id');
			$table->string('name')->index('dirname_name')->comment('用户云相册顶级目录');
			$table->integer('count')->unsigned()->default(0)->comment('包含文件数量');
			$table->boolean('active')->default(1)->comment('是否正常，1正常0删除');
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
		Schema::drop('zx_cloud_storage_folder');
	}

}
