<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeAudioFolderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_audio_folder', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('name')->index('dirname_name')->comment('音频目录');
			$table->integer('count')->unsigned()->default(0)->comment('包含文件数量');
			$table->integer('sort')->unsigned()->comment('排序');
			$table->boolean('active')->default(0)->comment('是否正常，1正常0待审批2删除');
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
		Schema::drop('zx_make_audio_folder');
	}

}
