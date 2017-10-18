<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCloudStorageFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cloud_storage_file', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('list_user_id')->comment('用户id');
			$table->string('name')->comment('文件的显示名称');
			$table->string('address')->nullable()->default('')->comment('用户资源的网络地址');
			$table->string('screenshot')->default('')->comment('如果为视频，截屏，第一帧');
			$table->bigInteger('folder_id')->unsigned()->index('list_dirname_id')->comment('所属目录');
			$table->boolean('type')->default(1)->comment('用户上传资源的类型，0视频，1图片，2其他');
			$table->boolean('format')->default(1)->comment('文件格式，1图片，2视频，3为mp3,4为PPT、doc，5为其他');
			$table->string('extension', 20)->nullable()->comment('文件的扩展名');
			$table->float('size', 10, 0)->nullable()->comment('文件大小');
			$table->boolean('active')->nullable()->default(1)->comment('是否正常，1正常0删除，删除文件在回收站保存七天，七天后自动删除');
			$table->integer('date')->unsigned()->comment('资源上传的年月日');
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
		Schema::drop('zx_cloud_storage_file');
	}

}
