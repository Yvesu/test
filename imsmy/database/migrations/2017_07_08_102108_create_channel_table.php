<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChannelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('channel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->comment('名称');
			$table->string('ename')->comment('英文名称');
			$table->string('icon');
			$table->string('hash_icon');
			$table->boolean('active');
			$table->integer('forwarding_time')->default(0);
			$table->integer('comment_time')->default(0);
			$table->integer('work_count')->default(0);
			$table->boolean('sort')->nullable()->comment('频道排序，可在后台修改');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('channel');
	}

}
