<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeFilterDownloadLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_filter_download_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('file_id')->unsigned()->comment('资源id');
			$table->bigInteger('user_id')->unsigned()->comment('下载资源的用户id');
			$table->integer('time_add')->nullable()->comment('上传时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_make_filter_download_log');
	}

}
