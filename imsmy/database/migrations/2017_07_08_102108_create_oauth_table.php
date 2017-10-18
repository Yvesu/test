<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('oauth', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('oauth_user_id_foreign');
			$table->string('oauth_name')->comment('登录方式(微博、微信、qq等)');
			$table->string('oauth_nickname')->comment('第三方的昵称名字');
			$table->string('oauth_id')->comment('第三方返回的oauth_id');
			$table->string('oauth_access_token')->comment('第三方返回的授权令牌，Access_Token');
			$table->dateTime('oauth_expires')->nullable()->comment('第三方返回的access_token的有效期，单位为秒');
			$table->boolean('status')->nullable()->default(0)->comment('用户状态，0表示正常，1表示拉黑');
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
		Schema::drop('oauth');
	}

}
