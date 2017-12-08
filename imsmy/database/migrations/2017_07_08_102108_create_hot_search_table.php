<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHotSearchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hot_search', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('hot_word')->nullable()->comment('热搜词');
			$table->boolean('active')->default(0)->comment('0表示未审批，1表示正常，2表示屏蔽');
			$table->integer('sort')->default(0)->comment('用来做排序的索引');
			$table->integer('time_add')->nullable();
			$table->integer('time_update')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('hot_search');
	}

}
