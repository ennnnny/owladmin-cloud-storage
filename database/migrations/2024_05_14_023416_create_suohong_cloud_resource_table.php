<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suohong_cloud_resource', function (Blueprint $table) {
            $table->comment('云资源表');
            $table->increments('id');
            $table->string('title')->default(null)->comment('名称');
            $table->integer('size')->default(0)->comment('大小（字节）');
            $table->string('url',1000)->comment('资料URL');
            $table->string('extension',100)->default(null)->comment('扩展名');
            $table->tinyInteger('driver')->default(1)->comment('类型（1：图片；2：文档；3：视频；4：音频；5：其他；）');
            $table->bigInteger('storage_id')->comment('存储ID');
            $table->bigInteger('created_user')->comment('创建人');
            $table->bigInteger('deleted_user')->comment('删除人');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suohong_cloud_resource');
    }
};
