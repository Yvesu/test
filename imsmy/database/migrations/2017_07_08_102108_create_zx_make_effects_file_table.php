<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZxMakeEffectsFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zx_make_effects_file', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('user_id')->unsigned()->index('chartlet_user_id')->comment('上传的用户id');
			$table->string('name')->comment('文件的显示名称');
			$table->string('intro')->comment('文件的介绍');
			$table->string('address')->default('')->comment('资源的网络地址');
			$table->string('high_address')->nullable()->default('')->comment('高清资源的网络地址');
			$table->string('super_address')->nullable()->default('')->comment('超清资源的网络地址');
			$table->string('preview_address')->default('')->comment('预览资源的网络地址');
			$table->string('cover')->default('')->comment('封面');
			$table->bigInteger('folder_id')->unsigned()->index('chartlet_folder_id')->comment('所属目录');
			$table->float('duration', 10, 0)->unsigned()->default(0)->comment('文件时长');
			$table->bigInteger('size')->unsigned()->default(0)->comment('贴图文件的大小');
			$table->boolean('attention')->default(0)->comment('下载是否要求关注对方，0不需要，1需要');
			$table->integer('integral')->unsigned()->default(0)->comment('下载需要积分的数量，0免费');
			$table->integer('cost')->unsigned()->default(0)->comment('下载需要的费用，单位人民币，0免费');
			$table->integer('count')->unsigned()->default(0)->comment('下载量');
			$table->bigInteger('sort')->unsigned()->comment('排列顺序');
			$table->boolean('recommend')->default(0)->comment('是否推荐，0不推荐，1推荐');
			$table->boolean('active')->default(0)->comment('是否正常，0待审批，1正常，2删除，删除文件在回收站保存七天，七天后自动删除');
			$table->integer('time_add')->unsigned()->default(0)->comment('上传时间');
			$table->integer('time_update')->unsigned()->default(0)->comment('资源的修改时间，可作为删除7天的标识');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zx_make_effects_file');
	}

}
