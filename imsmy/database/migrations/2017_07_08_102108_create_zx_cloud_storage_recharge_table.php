<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCloudStorageRechargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_cloud_storage_recharge', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('space_user_id')->comment('用户id');
			$table->bigInteger('total_space')->unsigned()->default(10737418240)->comment('默认10G');
			$table->integer('fee')->unsigned()->default(0)->comment('费用');
			$table->boolean('recharge_method')->default(1)->comment('充值方式，1支付宝，2微信');
			$table->integer('time_from')->unsigned()->comment('有效期开始日期');
			$table->integer('time_end')->unsigned()->comment('有效期结束日期');
			$table->integer('time_add')->nullable();
			$table->integer('time_update')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_cloud_storage_recharge');
	}

}
