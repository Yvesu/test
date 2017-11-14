<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdministratorBTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('administrator_b', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('email', 140)->unique();
			$table->string('password');
			$table->string('g_r_ids', 100)->nullable()->comment('权限组id');
			$table->string('avatar');
			$table->boolean('sex')->default(1);
			$table->integer('position_id')->unsigned()->nullable()->comment('职位外键');
			$table->string('permissions')->nullable()->comment('个人权限');
			$table->string('annexURL')->nullable();
			$table->string('ID_card_URL');
			$table->string('phone');
			$table->string('name');
			$table->string('secondary_contact_name');
			$table->string('secondary_contact_phone');
			$table->string('secondary_contact_relationship');
			$table->bigInteger('user_id')->unsigned()->nullable()->comment('绑定的APP端注册用户id');
			$table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('administrator_b');
	}

}
