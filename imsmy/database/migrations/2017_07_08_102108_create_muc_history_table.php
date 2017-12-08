<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMucHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('muc_history', function(Blueprint $table)
		{
			$table->char('room_name', 128);
			$table->integer('event_type')->nullable();
			$table->bigInteger('timestamp')->nullable();
			$table->string('sender_jid', 2049)->nullable();
			$table->char('sender_nickname', 128)->nullable();
			$table->text('body', 65535)->nullable();
			$table->boolean('public_event')->nullable();
			$table->text('msg', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('muc_history');
	}

}
