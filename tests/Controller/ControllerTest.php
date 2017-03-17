<?php

use Silex\WebTestCase;

class ControllerTest extends WebTestCase
{
    public function testGetHomepage()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Welcome', $crawler->filter('body')->text());
    }

    public function testPostConfigFile()
    {

        copy( __DIR__.'/testFile.json', __DIR__.'/configFile.json');

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            __DIR__.'/configFile.json',
            'configFile.json',
            'application/json'
        );

        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request(
            'POST',
            '/api/calculate',
            array(),
            array('file' => $file)
        );

        var_dump($client->getResponse()->getContent());

    }

    public function createApplication()
    {
        $app = require __DIR__.'/../../src/app.php';
        require __DIR__.'/../../config/dev.php';
        require __DIR__.'/../../src/controllers.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
