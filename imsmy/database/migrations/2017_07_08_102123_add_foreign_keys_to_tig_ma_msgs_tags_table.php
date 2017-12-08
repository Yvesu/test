<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTigMaMsgsTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tig_ma_msgs_tags', function(Blueprint $table)
		{
			$table->foreign('msg_id', 'tig_ma_msgs_tags_ibfk_1')->references('msg_id')->on('tig_ma_msgs')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('tag_id', 'tig_ma_msgs_tags_ibfk_2')->references('tag_id')->on('tig_ma_tags')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tig_ma_msgs_tags', function(Blueprint $table)
		{
			$table->dropForeign('tig_ma_msgs_tags_ibfk_1');
			$table->dropForeign('tig_ma_msgs_tags_ibfk_2');
		});
	}

}
