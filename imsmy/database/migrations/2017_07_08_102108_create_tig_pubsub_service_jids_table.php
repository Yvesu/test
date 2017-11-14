<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubServiceJidsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_service_jids', function(Blueprint $table)
		{
			$table->bigInteger('service_id', true);
			$table->string('service_jid', 2049)->index('service_jid');
			$table->char('service_jid_sha1', 40)->unique('service_jid_sha1');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pubsub_service_jids');
	}

}
