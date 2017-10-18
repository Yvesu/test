<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserCollectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_collections', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->nullable()->index('user_id')->comment('用户id');
			$table->bigInteger('type')->unsigned()->nullable()->default(1)->comment('1为话题，');
			$table->bigInteger('type_id')->unsigned()->nullable()->comment('话题id');
			$table->boolean('status')->nullable()->default(1)->comment('1为正常，0为取消收藏');
			$table->integer('time_add')->unsigned()->nullable()->comment('添加时间');
			$table->integer('time_update')->unsigned()->nullable()->comment('更新时间戳');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_collections');
	}

}
