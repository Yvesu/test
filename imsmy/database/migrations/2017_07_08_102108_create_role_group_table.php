<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRoleGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('role_group', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->string('name', 100)->comment('名称');
			$table->string('intro', 100)->comment('描述');
			$table->string('r_m_ids', 500)->default('')->comment('权限菜单IDs  以逗号间隔');
			$table->integer('admin_id')->comment('添加管理员的ID');
			$table->integer('audit_admin_id')->default(0)->comment('管理员的ID,用于管理这个组或分发改组权限');
			$table->integer('pid')->default(0)->comment('父级ID ');
			$table->integer('time_add')->default(0)->comment('添加时间');
			$table->boolean('status')->default(1)->comment('0表未生效  1表生效');
			$table->integer('time_update')->default(0)->comment('更新时间');
			$table->string('path', 500)->default('0,')->comment('权限组路径');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('role_group');
	}

}
