<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityOrderUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_order_user', function(Blueprint $table)
		{
			$table->increments('id')->comment('状态id');
			$table->bigInteger('activity_id')->unsigned()->comment('activity表中的id');
			$table->bigInteger('order_id')->unsigned()->comment('支付订单号');
			$table->float('account', 10)->comment('应付金额');
			$table->boolean('payment_type')->default(0)->comment('支付方式，0未支付，1支付宝，2微信，3其他');
			$table->boolean('status')->default(0)->comment('支付状态，0未支付，1已支付');
			$table->string('details')->comment('费用的详情说明');
			$table->boolean('active')->default(1)->comment('状态，1正常，0删除');
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
		Schema::drop('activity_order_user');
	}

}
