<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxCompanyAccountExpendLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_company_account_expend_log', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键ID');
			$table->bigInteger('user_id')->comment('用户ID');
			$table->boolean('type')->default(0)->comment('收支类型，1为参与赛事收入，2为从粉丝处分成，3为发布赛事失败退回支出');
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
		Schema::drop('zx_company_account_expend_log');
	}

}
