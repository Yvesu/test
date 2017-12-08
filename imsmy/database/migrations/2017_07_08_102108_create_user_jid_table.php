<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserJidTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_jid', function(Blueprint $table)
		{
			$table->bigInteger('jid_id', true)->unsigned();
			$table->char('jid_sha', 128)->unique('jid_sha');
			$table->string('jid', 2049)->index('jid');
			$table->integer('history_enabled')->nullable()->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_jid');
	}

}
