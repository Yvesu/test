<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePrivateLetterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('private_letter', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('from')->unsigned()->index('message_from_index')->comment('发私信者id');
			$table->bigInteger('to')->unsigned()->index('message_to_index')->comment('接收私信者id');
			$table->string('content')->comment('私信内容');
			$table->boolean('type')->nullable()->default(0)->comment('0代表未读，1代表已读');
			$table->boolean('delete_from')->nullable()->default(0)->comment('0为未删除，1为发信人删除');
			$table->boolean('delete_to')->nullable()->default(0)->comment('0为未删除，1为发信人删除');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('private_letter');
	}

}
