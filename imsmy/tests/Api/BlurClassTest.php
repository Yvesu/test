<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BlurClassTest extends TestCase
{
    /**
     * 测试 获取全部滤镜类型
     */
    public function testIndex()
    {
        $this->get('api/blur-classes')
             ->assertResponseOk();
    }

    /**
     * 测试 获取ID滤镜下所有滤镜详细信息
     */
    public function testInstall()
    {
        $this->get('api/blur-classes/4')
             ->assertResponseOk();
    }

    public function testPreview()
    {
        $this->get('api/blur-classes/4/preview')
            ->assertResponseOk();
    }
}
