<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BlurTest extends TestCase
{
    /**
     * 测试 获取分页滤镜
     */
    public function testIndex()
    {
        $this->get('api/blurs')
             ->assertResponseOk();
    }

    /**
     * 测试 通过ID获取单条滤镜
     */
    public function testShow()
    {
        $this->get('api/blurs/1')
             ->assertResponseStatus(404);
    }
}
