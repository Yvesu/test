<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClusterNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cluster_nodes', function(Blueprint $table)
		{
			$table->string('hostname', 200)->primary();
			$table->string('password');
			$table->timestamp('last_update')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('port')->nullable();
			$table->float('cpu_usage', 10, 0)->unsigned();
			$table->float('mem_usage', 10, 0)->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cluster_nodes');
	}

}
