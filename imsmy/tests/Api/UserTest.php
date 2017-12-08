<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function getToken()
    {
        $response = $this->call('GET','api/users/authenticate?username=13840313484&password=123456');
        return json_decode($response->getContent())->token;
    }

    public function testAvatar()
    {
        /*$this->post(
                'api/users/1/avatar?token=' . $this->getToken(),
                [],
                ['Authorization' => 'Qbox e']
             )
             ->assertResponseStatus(401);*/
    }
}
