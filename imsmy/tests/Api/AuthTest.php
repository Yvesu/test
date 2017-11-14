<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    public function getToken()
    {
        $response = $this->call('GET','api/users/authenticate?username=13840313484&password=123456');
        return json_decode($response->getContent())->token;
    }
    /**
     * 测试 短信认证
     *
     * @return void
     */
    public function testSMSVerity()
    {
        $this->post(
            '/api/users/sms-verify',
            [
                'phone' => 13840313484,
                'zone'  => 86,
                'code'  => 1234
            ])
            ->assertResponseStatus(468);
    }

    /**
     * 测试 用户授权
     */
    public function testAuthenticate()
    {
        $this->get('api/users/authenticate?username=13840313484&password=123456')
             ->seeJsonStructure(['token']);
    }

    public function testRegister()
    {
        
    }

    public function testResetPassword()
    {
        
    }

    /**
     * 测试 用户获取自己信息
     */
    public function testGetAuthenticateUser()
    {
        $this->get('api/users/me?token=' . $this->getToken())
             ->assertResponseOk();
    }
}
