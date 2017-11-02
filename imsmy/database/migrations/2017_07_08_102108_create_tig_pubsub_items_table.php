<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPubsubItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pubsub_items', function(Blueprint $table)
		{
			$table->bigInteger('node_id')->index('node_id_2');
			$table->string('id', 191);
			$table->char('id_sha1', 40);
			$table->dateTime('creation_date')->nullable();
			$table->bigInteger('publisher_id')->nullable()->index('publisher_id');
			$table->dateTime('update_date')->nullable();
			$table->text('data', 16777215)->nullable();
			$table->primary(['node_id','id_sha1']);
			$table->index(['node_id','id'], 'node_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pubsub_items');
	}

}
