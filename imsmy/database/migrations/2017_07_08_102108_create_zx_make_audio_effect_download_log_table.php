<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeAudioEffectDownloadLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_audio_effect_download_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('file_id')->unsigned()->comment('音频id');
			$table->bigInteger('user_id')->unsigned()->comment('下载音频的用户id');
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
		Schema::drop('zx_make_audio_effect_download_log');
	}

}
