<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserLeaseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_lease', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->comment('用户id');
			$table->integer('type_id')->unsigned()->index('demand_job_id')->comment('zx_user_lease_type表，类型id');
			$table->integer('cost')->default(0)->comment('费用');
			$table->boolean('cost_type')->default(0)->comment('0代表天，1代表月');
			$table->string('ad')->nullable()->comment('地区');
			$table->string('ad_details')->nullable()->comment('详细地址');
			$table->string('accessory')->nullable()->default('')->comment('用户上传附件，多张图片');
			$table->boolean('active')->default(1)->comment('状态，1为正常，2为后台或自己删除');
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
		Schema::drop('zx_user_lease');
	}

}
