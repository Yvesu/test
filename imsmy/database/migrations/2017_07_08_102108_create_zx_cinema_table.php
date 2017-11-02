<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCinemaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cinema', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->comment('院线名称');
			$table->string('intro')->nullable()->comment('院线简介');
			$table->string('background_image')->nullable()->comment('背景图片地址');
			$table->boolean('active')->default(0)->comment('0表示未审批，1表示正常');
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
		Schema::drop('zx_cinema');
	}

}
