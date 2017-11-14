<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserVerifyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_verify', function(Blueprint $table)
		{
			$table->increments('id');
			$table->bigInteger('user_id')->unsigned()->unique('verify_user_id')->comment('用户id');
			$table->boolean('verify')->nullable()->default(1)->comment('认证类型，1为个人认证，2为企业认证');
			$table->string('verify_info')->nullable()->comment('认证信息');
			$table->boolean('verify_status')->nullable()->default(0)->comment('认证状态，0为未审批，1为通过，2为不通过');
			$table->integer('time_add')->nullable()->comment('提交时间');
			$table->integer('time_update')->nullable()->comment('审批更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_verify');
	}

}
