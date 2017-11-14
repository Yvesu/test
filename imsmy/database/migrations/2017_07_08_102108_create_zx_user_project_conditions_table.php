<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectConditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project_conditions', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('amount')->nullable()->comment('投资价位');
			$table->string('content')->nullable()->comment('相应价位的条件');
			$table->boolean('active')->default(1)->comment('状态，1为正常，0为后台删除');
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
		Schema::drop('zx_user_project_conditions');
	}

}
