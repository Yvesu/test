<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxTopicManageLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_topic_manage_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('admin_id')->unsigned()->index('verify_admin_id')->comment('后台操作人员的id');
			$table->bigInteger('data_id')->index('verify_verify_id')->comment('topic 表中的id');
			$table->boolean('active')->nullable()->default(1)->comment('处理类型，1为正常，2为屏蔽');
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
		Schema::drop('zx_topic_manage_log');
	}

}
