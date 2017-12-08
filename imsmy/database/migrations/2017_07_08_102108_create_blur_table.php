<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlurTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blur', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name_zh');
			$table->string('name_en');
			$table->integer('blur_class_id')->unsigned()->nullable()->comment('滤镜类型外键');
			$table->string('parameter', 4096)->comment('json格式');
			$table->string('sequence_diagram')->nullable();
			$table->string('dynamic_image')->nullable();
			$table->string('background');
			$table->boolean('shutter_speed')->default(0);
			$table->string('face_tracking')->nullable();
			$table->boolean('gravity_sensing')->default(0);
			$table->boolean('active')->default(2);
			$table->decimal('xAlign', 5);
			$table->decimal('yAlign', 5);
			$table->boolean('scaling_ratio');
			$table->string('audio')->nullable();
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
		Schema::drop('blur');
	}

}
