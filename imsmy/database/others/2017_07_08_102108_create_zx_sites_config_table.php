<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxSitesConfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_sites_config', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('active')->default(0)->comment('本条信息的状态，0为待审批，1为正常，2为删除');
			$table->string('title')->nullable()->default('')->comment('网站标题');
			$table->string('icon')->nullable()->comment('网站图标');
			$table->string('hash_icon')->nullable()->comment('网站图标，哈希加密');
			$table->string('keywords')->nullable()->comment('搜索关键词');
			$table->bigInteger('admin_id')->unsigned()->comment('提交人');
			$table->integer('time_add')->nullable()->comment('添加时间');
			$table->integer('time_update')->nullable()->comment('修改时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_sites_config');
	}

}
