<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigMaMsgsTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_ma_msgs_tags', function(Blueprint $table)
		{
			$table->bigInteger('msg_id')->unsigned()->index('tig_ma_msgs_tags_msg_id');
			$table->bigInteger('tag_id')->unsigned()->index('tig_ma_msgs_tags_tag_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_ma_msgs_tags');
	}

}
