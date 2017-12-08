<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserJpushTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_jpush', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('user_jpush_user_id')->comment('用户id，user表');
			$table->bigInteger('jpush_id')->unsigned()->index('user_jpush_jpush_id')->comment('极光推送的注册 ID');
			$table->boolean('active')->nullable()->default(1)->comment('状态，1正常，0非正常');
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
		Schema::drop('zx_user_jpush');
	}

}
