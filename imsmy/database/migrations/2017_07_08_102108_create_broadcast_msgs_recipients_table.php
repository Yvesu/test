<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBroadcastMsgsRecipientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('broadcast_msgs_recipients', function(Blueprint $table)
		{
			$table->string('msg_id', 128);
			$table->bigInteger('jid_id')->unsigned();
			$table->primary(['msg_id','jid_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('broadcast_msgs_recipients');
	}

}
