<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUploadFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_upload_files', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->string('name')->comment('名称');
			$table->string('url')->comment('七牛上的URL');
			$table->integer('time_add')->unsigned()->comment('添加时间');
			$table->integer('time_update')->unsigned()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_upload_files');
	}

}
