<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShortNewsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('short_news', function(Blueprint $table)
		{
			$table->bigInteger('snid', true)->unsigned();
			$table->timestamp('publishing_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index('publishing_time');
			$table->string('news_type', 10)->nullable()->index('news_type');
			$table->string('author', 128)->index('author');
			$table->string('subject', 128);
			$table->string('body', 1024);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('short_news');
	}

}
