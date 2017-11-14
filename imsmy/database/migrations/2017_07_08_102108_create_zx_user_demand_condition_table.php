<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserDemandConditionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_demand_condition', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('job_condition')->default('')->comment('岗位要求');
			$table->integer('time_add');
			$table->integer('time_update');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_demand_condition');
	}

}
