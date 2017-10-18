<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigMaMsgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_ma_msgs', function(Blueprint $table)
		{
			$table->foreign('buddy_id', 'tig_ma_msgs_ibfk_1')->references('jid_id')->on('tig_ma_jids')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('owner_id', 'tig_ma_msgs_ibfk_2')->references('jid_id')->on('tig_ma_jids')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_ma_msgs', function(Blueprint $table)
		{
			$table->dropForeign('tig_ma_msgs_ibfk_1');
			$table->dropForeign('tig_ma_msgs_ibfk_2');
		});
	}

}
