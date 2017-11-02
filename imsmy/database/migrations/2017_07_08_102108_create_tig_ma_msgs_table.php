<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTigMaMsgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tig_ma_msgs', function(Blueprint $table)
		{
			$table->bigInteger('msg_id', true)->unsigned();
			$table->bigInteger('owner_id')->unsigned()->nullable()->index('owner_id');
			$table->bigInteger('buddy_id')->unsigned()->nullable()->index('buddy_id');
			$table->timestamp('ts')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->smallInteger('direction')->nullable();
			$table->string('type', 10)->nullable();
			$table->text('body', 65535)->nullable();
			$table->text('msg', 65535)->nullable();
			$table->index(['owner_id','buddy_id'], 'owner_id_2');
			$table->index(['owner_id','ts','buddy_id'], 'owner_id_3');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tig_ma_msgs');
	}

}
