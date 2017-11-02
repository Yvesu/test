<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxAgreementTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_agreement', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('type')->default(1)->comment('类型，1为注册协议，2为举报规范，3为发起协议，4为投资协议，5为租赁协议');
			$table->string('title')->nullable()->comment('标题');
			$table->text('content', 65535)->comment('协议内容');
			$table->boolean('active')->default(0)->comment('是否为激活状态');
			$table->integer('time_add')->unsigned();
			$table->integer('time_update')->unsigned();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_agreement');
	}

}
