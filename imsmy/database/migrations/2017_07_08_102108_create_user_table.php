<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned()->comment('主键id');
			$table->string('nickname')->comment('昵称');
			$table->string('avatar')->nullable()->comment('像key值，请以temp/开头');
			$table->string('hash_avatar')->nullable()->comment('头像hash值');
			$table->string('video_avatar')->nullable();
			$table->boolean('sex')->nullable()->default(0)->comment('性别 0为女，1为男');
			$table->string('cover')->nullable()->default('')->comment('封面图');
			$table->boolean('verify')->nullable()->default(0)->comment('认证，0为未认证，1为个人认证，2为企业认证');
			$table->string('verify_info')->nullable()->default('')->comment('认证信息');
			$table->string('honor')->nullable()->comment('荣誉');
			$table->string('signature')->nullable()->comment('签名');
			$table->string('background')->nullable()->comment('背景key值');
			$table->string('location')->nullable()->comment('位置信息');
			$table->integer('location_id')->nullable()->comment('对应location表中的id');
			$table->integer('nearby_id')->nullable()->default(0)->comment('对应zx_adcode表中的id');
			$table->string('birthday', 20)->nullable()->comment('生日');
			$table->string('phone_model', 20)->nullable()->comment('手机型号');
			$table->string('phone_serial', 50)->nullable()->comment('手机序列号，作为验证是否更换手机登录');
			$table->string('phone_sdk_int', 20)->nullable()->comment('手机版本号');
			$table->string('umeng_device_token', 100)->nullable()->comment('友盟的device token,每次登陆核对更新');
			$table->boolean('xmpp')->nullable()->default(0)->comment('xmpp是非开启陌生人消息的即时通讯，0为默认不开通，1为开通');
			$table->boolean('advertisement')->nullable()->default(1)->comment('1为默认开启广告，0为关闭广告，用户看视频及发视频加广告');
			$table->boolean('status')->nullable()->default(0)->comment('用户状态，0表示正常，1表示拉黑');
			$table->boolean('stranger_comment')->default(1)->comment('是否允许陌生人评论，1允许，0禁止（只有关注者可以评论）');
			$table->boolean('stranger_at')->default(1)->comment('是否允许陌生人@，1允许，0禁止（只有关注者可以@）');
			$table->boolean('stranger_private_letter')->default(1)->comment('是否允许陌生人私信，1允许，0禁止（只有关注者可以）');
			$table->boolean('location_recommend')->default(1)->comment('是否通过位置推荐我，1允许，0禁止');
			$table->boolean('search_phone')->default(1)->comment('是否通过手机号搜索我，1允许，0禁止');
			$table->boolean('new_message_comment')->default(1)->comment('新评论，是否开启新消息提醒，1允许，0禁止');
			$table->boolean('new_message_fans')->default(1)->comment('新粉丝，是否开启新消息提醒，1允许，0禁止');
			$table->boolean('new_message_like')->default(1)->comment('点赞，是否开启新消息提醒，1允许，0禁止');
			$table->integer('num_attention')->default(0)->comment('粉丝数');
			$table->timestamp('last_token')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
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
		Schema::drop('user');
	}

}
