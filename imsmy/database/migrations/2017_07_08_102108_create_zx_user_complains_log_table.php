<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserComplainsLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_complains_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('admin_id')->unsigned()->index('verify_admin_id')->comment('后台操作人员的id');
			$table->bigInteger('complain_id')->index('verify_verify_id')->comment('zx_user_verify 表中的id');
			$table->boolean('type')->nullable()->default(1)->comment('处理类型，1为正常，2为屏蔽');
			$table->integer('time_add')->nullable()->comment('操作时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_complains_log');
	}

}
