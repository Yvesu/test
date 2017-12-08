<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxHelpContentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_help_content', function(Blueprint $table)
		{
			$table->increments('id')->comment('主键');
			$table->text('content', 65535)->nullable()->comment('帮助的具体内容');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_help_content');
	}

}
