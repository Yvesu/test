<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityExtensionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_extension', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('activity_id')->unique('activity_id')->comment('活动id');
			$table->string('screen_shot')->nullable()->comment('视频截图');
			$table->string('video')->nullable()->comment('视频URL');
			$table->integer('time_add')->unsigned()->nullable();
			$table->integer('time_update')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_extension');
	}

}
