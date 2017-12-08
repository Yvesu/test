<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_nodes', function(Blueprint $table)
		{
			$table->bigInteger('node_id', true);
			$table->bigInteger('service_id')->index('service_id');
			$table->string('name', 191)->index('name');
			$table->char('name_sha1', 40);
			$table->integer('type');
			$table->string('title', 1000)->nullable();
			$table->text('description', 16777215)->nullable();
			$table->bigInteger('creator_id')->nullable()->index('creator_id');
			$table->dateTime('creation_date')->nullable();
			$table->text('configuration', 16777215)->nullable();
			$table->bigInteger('collection_id')->nullable()->index('collection_id');
			$table->unique(['service_id','name_sha1'], 'service_id_3');
			$table->index(['service_id','name'], 'service_id_2');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pubsub_nodes');
	}

}
