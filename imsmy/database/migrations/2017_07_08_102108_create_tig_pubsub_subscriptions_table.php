<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubSubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_subscriptions', function(Blueprint $table)
		{
			$table->bigInteger('node_id')->index('node_id');
			$table->bigInteger('jid_id')->index('jid_id');
			$table->string('subscription', 20);
			$table->string('subscription_id', 40);
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
		Schema::drop('tig_pubsub_subscriptions');
	}

}
