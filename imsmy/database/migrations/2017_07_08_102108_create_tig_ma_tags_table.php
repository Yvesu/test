<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigMaTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_ma_tags', function(Blueprint $table)
		{
			$table->bigInteger('tag_id', true)->unsigned();
			$table->string('tag')->nullable();
			$table->bigInteger('owner_id')->unsigned()->index('tig_ma_tags_owner_id');
			$table->unique(['owner_id','tag'], 'tig_ma_tags_tag_owner_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_ma_tags');
	}

}
