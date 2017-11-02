<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectTeamTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project_team', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('project_id')->unsigned()->index('project_project_id')->comment('投资的项目id');
			$table->bigInteger('user_id')->index('project_user_id')->comment('项目团队的id');
			$table->string('role', 100)->nullable()->comment('在该项目中担任的职务');
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
		Schema::drop('zx_user_project_team');
	}

}
