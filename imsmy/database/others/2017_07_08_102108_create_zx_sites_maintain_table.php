<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxSitesMaintainTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_sites_maintain', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('status')->default(1)->comment('网站状态，1为正常，2为维护状态');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_sites_maintain');
	}

}
