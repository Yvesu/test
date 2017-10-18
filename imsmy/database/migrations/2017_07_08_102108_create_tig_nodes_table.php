<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_nodes', function(Blueprint $table)
		{
			$table->bigInteger('nid', true)->unsigned();
			$table->bigInteger('parent_nid')->unsigned()->nullable()->index('parent_nid');
			$table->bigInteger('uid')->unsigned()->index('uid');
			$table->string('node')->index('node');
			$table->unique(['parent_nid','uid','node'], 'tnode');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_nodes');
	}

}
