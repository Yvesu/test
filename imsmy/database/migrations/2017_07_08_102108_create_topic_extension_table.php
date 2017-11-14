<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTopicExtensionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('topic_extension', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('topic_id')->unique('topic_id')->comment('话题id');
			$table->string('screen_shot')->nullable()->comment('视频截图');
			$table->string('video')->nullable()->comment('视频URL');
			$table->string('photo')->nullable()->comment('图片字符串URL');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('topic_extension');
	}

}
