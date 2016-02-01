<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VootControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/voot');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testSmokeGroupsIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/voot/user/1/groups');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testSmokeGroupIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/voot/user/1/groups/1');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
