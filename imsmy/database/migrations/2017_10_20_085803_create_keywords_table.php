<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('keywords', function (Blueprint $table) {
            $table->increments('id')->comment('主键');
            $table->char('keyword', 50)->comment('关键字');
            $table->integer('count_sum')->unsined()->default(0)->comment('总搜索数量');
            $table->integer('count_day')->unsined()->default(0)->comment('日搜索数量');
            $table->integer('count_week')->unsined()->default(0)->comment('周搜索数量');
            $table->enum('type', ['0', '1'])->default('0')->comment('类型');
            $table->timestamps('create_at');
            $table->timestamps('update_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('keywords');
    }
}
