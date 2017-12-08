<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CloudStorageTest extends TestCase
{
    /**
     * 测试 获取上传文件的token
     */
    public function testToken()
    {
        $this->get('api/cloud-storage/token')
             ->seeJsonStructure(['token']);
    }

    /**
     * 测试 获取私有空间下载URL
     */
    public function testPrivateDownloadUrl()
    {
        $this->get('api/cloud-storage/private-download-url?key=test')
             ->seeJsonStructure(['url']);
    }

    /**
     * 测试 删除云存储某个文件
     */
    public function testDeleteFile()
    {
        $this->delete('file?key=test')
             ->assertResponseStatus(404);
    }

    /**
     * 测试 删除云存储某个文件夹
     */
    public function testDeleteDirectory()
    {
        $this->delete('directory?prefix=test')
             ->assertResponseStatus(404);
    }


}
