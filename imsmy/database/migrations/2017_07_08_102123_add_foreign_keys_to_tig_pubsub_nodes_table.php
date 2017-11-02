<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigPubsubNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_pubsub_nodes', function(Blueprint $table)
		{
			$table->foreign('service_id', 'tig_pubsub_nodes_ibfk_1')->references('service_id')->on('tig_pubsub_service_jids')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('creator_id', 'tig_pubsub_nodes_ibfk_2')->references('jid_id')->on('tig_pubsub_jids')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('collection_id', 'tig_pubsub_nodes_ibfk_3')->references('node_id')->on('tig_pubsub_nodes')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_pubsub_nodes', function(Blueprint $table)
		{
			$table->dropForeign('tig_pubsub_nodes_ibfk_1');
			$table->dropForeign('tig_pubsub_nodes_ibfk_2');
			$table->dropForeign('tig_pubsub_nodes_ibfk_3');
		});
	}

}
