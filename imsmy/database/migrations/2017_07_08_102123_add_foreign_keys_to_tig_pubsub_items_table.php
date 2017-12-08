<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigPubsubItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_pubsub_items', function(Blueprint $table)
		{
			$table->foreign('node_id', 'tig_pubsub_items_ibfk_1')->references('node_id')->on('tig_pubsub_nodes')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('publisher_id', 'tig_pubsub_items_ibfk_2')->references('jid_id')->on('tig_pubsub_jids')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_pubsub_items', function(Blueprint $table)
		{
			$table->dropForeign('tig_pubsub_items_ibfk_1');
			$table->dropForeign('tig_pubsub_items_ibfk_2');
		});
	}

}
