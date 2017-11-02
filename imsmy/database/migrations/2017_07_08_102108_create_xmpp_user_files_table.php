<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXmppUserFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('xmpp_user_files', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('主键');
			$table->bigInteger('user_from')->unsigned()->comment('发送的用户id');
			$table->bigInteger('user_to')->unsigned()->comment('接收的用户id');
			$table->string('address')->comment('地址');
			$table->boolean('type')->comment('1图片2音频');
			$table->integer('time_add')->unsigned()->comment('添加时间');
			$table->integer('time_update')->unsigned()->comment('文件的更新时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('xmpp_user_files');
	}

}
