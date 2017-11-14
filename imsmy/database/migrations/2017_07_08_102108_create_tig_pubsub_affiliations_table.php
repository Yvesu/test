<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubAffiliationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_affiliations', function(Blueprint $table)
		{
			$table->bigInteger('node_id')->index('node_id');
			$table->bigInteger('jid_id')->index('jid_id');
			$table->string('affiliation', 20);
			$table->primary(['node_id','jid_id']);
			$table->unique(['node_id','jid_id'], 'node_id_2');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pubsub_affiliations');
	}

}
