<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigPairsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_pairs', function(Blueprint $table)
		{
			$table->foreign('uid', 'tig_pairs_constr_1')->references('uid')->on('tig_users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('nid', 'tig_pairs_constr_2')->references('nid')->on('tig_nodes')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_pairs', function(Blueprint $table)
		{
			$table->dropForeign('tig_pairs_constr_1');
			$table->dropForeign('tig_pairs_constr_2');
		});
	}

}
