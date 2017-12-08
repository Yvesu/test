<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigMaTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_ma_tags', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'tig_ma_tags_ibfk_1')->references('jid_id')->on('tig_ma_jids')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_ma_tags', function(Blueprint $table)
		{
			$table->dropForeign('tig_ma_tags_ibfk_1');
		});
	}

}
