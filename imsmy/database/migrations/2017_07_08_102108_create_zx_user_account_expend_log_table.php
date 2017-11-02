<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxUserAccountExpendLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_user_account_expend_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->bigInteger('user_id')->comment('用户ID');
			$table->bigInteger('user_to')->unsigned()->default(0)->comment('金额接收方的用户id');
			$table->boolean('type')->default(0)->comment('收支类型，1为赠送奖杯，2为发布赛事开支，3为分成给关注者');
			$table->bigInteger('type_id')->unsigned()->default(0)->comment('所在类型的id，赛事则为赛事表，');
			$table->float('num', 11)->unsigned()->default(0.00)->comment('资金支出情况');
			$table->string('intro')->default('0')->comment('说明');
			$table->integer('time_add')->default(0)->comment('添加时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_user_account_expend_log');
	}

}
