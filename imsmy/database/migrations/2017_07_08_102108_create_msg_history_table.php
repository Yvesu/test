<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMsgHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('msg_history', function(Blueprint $table)
		{
			$table->bigInteger('msg_id', true)->unsigned();
			$table->timestamp('ts')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('expired')->nullable()->index('expired');
			$table->bigInteger('sender_uid')->unsigned()->nullable();
			$table->bigInteger('receiver_uid')->unsigned();
			$table->integer('msg_type')->default(0);
			$table->string('message', 4096);
			$table->index(['sender_uid','receiver_uid'], 'sender_uid');
			$table->index(['receiver_uid','sender_uid'], 'receiver_uid');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('msg_history');
	}

}
