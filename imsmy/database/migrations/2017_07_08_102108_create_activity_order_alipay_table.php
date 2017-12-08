<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityOrderAlipayTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_order_alipay', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键id');
			$table->string('body')->comment('商品描述');
			$table->string('buyer_email')->comment('支付者的邮箱账号');
			$table->integer('buyer_id')->unsigned()->comment('买家支付宝用户号');
			$table->string('notify_id')->comment('通知校验ID');
			$table->integer('notify_time')->unsigned()->comment('通知时间');
			$table->string('notify_type')->comment('通知类型');
			$table->bigInteger('out_trade_no')->unsigned()->comment('商户订单号，mt_order表中的order_id');
			$table->string('seller_id', 50)->default('0')->comment('卖家支付宝用户号');
			$table->string('subject')->default('0')->comment('订单标题');
			$table->float('total_fee', 10)->unsigned()->comment('应付金额,mt_order表中的account');
			$table->string('trade_no', 100)->comment('支付宝交易号');
			$table->string('trade_status')->comment('交易状态');
			$table->string('sign')->comment('签名');
			$table->string('sign_type', 10)->comment('签名类型');
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
		Schema::drop('activity_order_alipay');
	}

}
