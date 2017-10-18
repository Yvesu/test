<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserGoldsLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_golds_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->integer('user_id')->comment('用户ID');
			$table->integer('num')->default(0)->comment('金币收支情况，-代表支出，+代表收入');
			$table->string('intro')->default('0')->comment('说明');
			$table->integer('time_add')->default(0)->comment('添加时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_golds_log');
	}

}
