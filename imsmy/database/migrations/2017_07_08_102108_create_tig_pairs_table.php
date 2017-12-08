<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigPairsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_pairs', function(Blueprint $table)
		{
			$table->bigInteger('nid')->unsigned()->nullable()->index('nid');
			$table->bigInteger('uid')->unsigned()->index('uid');
			$table->string('pkey')->index('pkey');
			$table->text('pval', 16777215)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_pairs');
	}

}
