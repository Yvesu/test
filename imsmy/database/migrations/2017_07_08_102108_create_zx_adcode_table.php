<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxAdcodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_adcode', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('citycode')->nullable()->comment('城市编码');
			$table->integer('adcode')->nullable()->comment('区编码');
			$table->string('street')->nullable()->comment('街道信息');
			$table->integer('time_add')->nullable();
			$table->integer('time_update')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_adcode');
	}

}
