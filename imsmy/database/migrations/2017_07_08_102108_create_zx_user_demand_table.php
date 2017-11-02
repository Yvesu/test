<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserDemandTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_demand', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->comment('用户id');
			$table->integer('job_id')->unsigned()->index('demand_job_id')->comment('zx_user_demand_job表，岗位id');
			$table->bigInteger('condition_id')->index('demand_condition_id')->comment('zx_user_demand_condition表id，岗位要求');
			$table->integer('film_id')->index('demand_flim_id')->comment('zx_film_menu表中id');
			$table->integer('cost')->default(0)->comment('费用');
			$table->boolean('cost_type')->default(0)->comment('0代表共计，1代表天，2代表月');
			$table->boolean('cost_unit')->default(0)->comment('0代表人民币，1代表美元');
			$table->string('city')->nullable()->comment('工作城市');
			$table->integer('from_time')->comment('开始时间');
			$table->integer('end_time')->comment('结束时间');
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
		Schema::drop('zx_user_demand');
	}

}
