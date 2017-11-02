<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserDemandCitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_demand_cities', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name')->comment('需求热门城市');
			$table->integer('count')->nullable()->default(1)->comment('这个城市发布需求的数量，也就是热门程度');
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
		Schema::drop('zx_user_demand_cities');
	}

}
