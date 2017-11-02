<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeFontFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_font_file', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->string('name', 50)->comment('字体名称');
			$table->string('cover')->comment('封面');
			$table->string('address')->comment('下载地址');
			$table->integer('sort')->unsigned()->comment('排序');
			$table->bigInteger('size')->unsigned()->default(0)->comment('贴图文件的大小');
			$table->boolean('active')->default(0)->comment('状态，0待审批，1审批通过，2待删除');
			$table->integer('time_add')->unsigned()->comment('添加时间');
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
		Schema::drop('zx_make_font_file');
	}

}
