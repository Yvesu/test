<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBroadcastMsgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('broadcast_msgs', function(Blueprint $table)
		{
			$table->string('id', 128)->primary();
			$table->dateTime('expired');
			$table->string('msg', 4096);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('broadcast_msgs');
	}

}
