<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('location', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('formattedAddress')->nullable();
			$table->string('country')->nullable();
			$table->string('province')->nullable()->default('0')->comment('省编码');
			$table->string('city')->nullable()->comment('市');
			$table->string('district')->nullable()->default('0')->comment('区');
			$table->string('township')->nullable()->comment('镇区，街道');
			$table->string('neighborhood')->nullable()->comment('所属社区');
			$table->string('building')->nullable();
			$table->integer('citycode')->nullable()->comment('城市编码');
			$table->integer('adcode')->nullable()->comment('区编码');
			$table->string('street')->nullable()->comment('街道信息');
			$table->string('number')->nullable()->comment('门牌号');
			$table->string('POIName')->nullable()->comment('兴趣区域');
			$table->string('AOIName')->nullable()->comment('所属兴趣点名称');
			$table->float('lng', 10, 0)->nullable()->comment('经度');
			$table->float('lat', 10, 0)->nullable()->comment('维度');
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
		Schema::drop('location');
	}

}
