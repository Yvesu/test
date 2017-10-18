<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubJidsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_jids', function(Blueprint $table)
		{
			$table->bigInteger('jid_id', true);
			$table->string('jid', 2049)->index('jid');
			$table->char('jid_sha1', 40)->unique('jid_sha1');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pubsub_jids');
	}

}
