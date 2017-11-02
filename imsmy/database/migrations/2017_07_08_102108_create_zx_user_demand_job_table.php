<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserDemandJobTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_demand_job', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->string('name', 100)->comment('名称');
			$table->integer('pid')->default(0)->comment('父级ID ');
			$table->string('path', 500)->default('0')->comment('父级路径  已逗号分隔 ');
			$table->boolean('active')->default(1)->comment('状态 1表示生效 0表失效');
			$table->integer('time_add')->default(0)->comment('添加时间');
			$table->integer('time_update')->default(0)->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_demand_job');
	}

}
