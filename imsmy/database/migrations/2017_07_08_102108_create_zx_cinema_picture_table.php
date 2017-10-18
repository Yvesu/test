<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCinemaPictureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cinema_picture', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('film_id')->comment('zx_integral_film 表id');
			$table->string('picture')->nullable()->comment('图片地址');
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
		Schema::drop('zx_cinema_picture');
	}

}
