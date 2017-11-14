<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRoleMenuTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('role_menu', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->string('name', 100)->comment('名称');
			$table->string('intro', 100)->comment('描述');
			$table->string('route', 100)->default('')->comment('控制器名称 User 必须同路由一致');
			$table->string('class_icon', 100)->default('')->comment('用户前台 徽章 icon ');
			$table->integer('pid')->default(0)->comment('父级ID ');
			$table->string('path', 500)->default('0,')->comment('父级路径  已逗号分隔 ');
			$table->boolean('status')->default(1)->comment('状态 1表示生效 0表失效 ');
			$table->boolean('show_nav')->default(0)->comment('导航是否展示 0不展示 1展示');
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
		Schema::drop('role_menu');
	}

}
