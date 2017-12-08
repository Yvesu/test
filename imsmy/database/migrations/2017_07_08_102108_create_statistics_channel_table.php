<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStatisticsChannelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statistics_channel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('channel_id')->unsigned()->index();
			$table->integer('forwarding_time')->default(0);
			$table->integer('comment_time')->default(0);
			$table->integer('work_count')->default(0);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statistics_channel');
	}

}
