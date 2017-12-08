<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlurClassTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blur_class', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name_zh');
			$table->string('name_en');
			$table->boolean('enable_icon')->default(1);
			$table->string('icon_sm')->nullable();
			$table->string('icon_lg')->nullable();
			$table->boolean('active')->default(1);
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
		Schema::drop('blur_class');
	}

}
