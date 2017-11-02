<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxFilmMenuTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_film_menu', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->comment('影片类型');
			$table->integer('sort')->unsigned()->nullable()->default(0)->comment('排序');
			$table->boolean('active')->default(1)->comment('状态，0为注销，1为正常');
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
		Schema::drop('zx_film_menu');
	}

}
