<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserProjectSupportTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_project_support', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('project_user_id')->comment('投资人的用户id');
			$table->bigInteger('project_id')->unsigned()->index('project_project_id')->comment('支持的项目id');
			$table->boolean('type')->default(1)->comment('支持的种类，1为1元，2为10元');
			$table->integer('amount')->nullable()->comment('支持金额');
			$table->string('comment')->nullable()->comment('用户备注，集资人可看');
			$table->integer('time_add')->unsigned()->nullable();
			$table->integer('time_update')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_project_support');
	}

}
