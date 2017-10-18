<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGpuimageValueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gpuimage_value', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('GPUImage_id')->unsigned()->comment('GPUImage外键');
			$table->string('name_zh');
			$table->string('name_en');
			$table->decimal('min', 10, 3)->nullable();
			$table->decimal('max', 10, 3)->nullable();
			$table->decimal('init', 10, 3)->nullable();
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
		Schema::drop('gpuimage_value');
	}

}
