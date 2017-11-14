<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxHelpNameTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_help_name', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->string('name')->default('')->comment('帮助的标题');
			$table->integer('content_id')->unsigned()->comment('帮助的具体内容id');
			$table->string('url')->nullable()->comment('详情的链接地址');
			$table->boolean('active')->default(0)->comment('0待审批，1审核通过，2删除');
			$table->integer('time_add')->unsigned()->comment('添加的时间');
			$table->integer('time_update')->unsigned()->comment('更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_help_name');
	}

}
