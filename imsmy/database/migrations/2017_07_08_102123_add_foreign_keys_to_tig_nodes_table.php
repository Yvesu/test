<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_nodes', function(Blueprint $table)
		{
			$table->foreign('uid', 'tig_nodes_constr')->references('uid')->on('tig_users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_nodes', function(Blueprint $table)
		{
			$table->dropForeign('tig_nodes_constr');
		});
	}

}
