<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectIntroTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project_intro', function(Blueprint $table)
		{
			$table->bigInteger('project_id')->unsigned()->index('project_project_id')->comment('动态id');
			$table->string('intro')->nullable()->default('')->comment('内容介绍');
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
		Schema::drop('zx_user_project_intro');
	}

}
