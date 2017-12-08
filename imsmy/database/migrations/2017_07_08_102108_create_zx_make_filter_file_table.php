<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeFilterFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_filter_file', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键id');
			$table->bigInteger('user_id')->unsigned()->comment('上传的用户id');
			$table->string('name')->comment('滤镜的名称');
			$table->string('cover')->nullable()->default('')->comment('封面');
			$table->text('content', 65535)->comment('滤镜参数');
			$table->bigInteger('folder_id')->unsigned()->comment('所属目录');
			$table->integer('count')->unsigned()->default(0)->comment('下载量');
			$table->integer('integral')->unsigned()->default(0)->comment('下载需要积分的数量，0免费');
			$table->bigInteger('sort')->unsigned()->comment('排列顺序');
			$table->boolean('recommend')->default(0)->comment('是否推荐，0不推荐，1推荐');
			$table->boolean('active')->default(0)->comment('是否正常，0待审批，1正常，2删除，删除文件在回收站保存七天，七天后自动删除');
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
		Schema::drop('zx_make_filter_file');
	}

}
