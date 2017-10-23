<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordFilterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('word_filter', function (Blueprint $table) {
            $table->increments('id')->comment('主键');
            $table->char('keyword', 50)->comment('关键字');
            $table->integer('count_sum')->unsined()->default(0)->comment('总搜索数量');
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
        Schema::drop('word_filter');
    }
}
