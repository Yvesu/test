<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserComplainsCauseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_complains_cause', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('content')->comment('用户举报原因');
			$table->boolean('active')->nullable()->default(1)->comment('0禁用，1在用');
			$table->integer('time_add')->unsigned()->nullable()->comment('添加时间');
			$table->integer('time_update')->unsigned()->nullable()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_complains_cause');
	}

}
