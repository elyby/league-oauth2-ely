<?php
namespace Ely\OAuth2\Client\Test;

use Ely\OAuth2\Client\ResourceOwner;

class ResourceOwnerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetId()
    {
        $this->assertEquals(1, $this->createModel()->getId());
    }

    public function testGetUuid()
    {
        $this->assertEquals('ffc8fdc9-5824-509e-8a57-c99b940fb996', $this->createModel()->getUuid());
    }

    public function testGetUsername()
    {
        $this->assertEquals('ErickSkrauch', $this->createModel()->getUsername());
    }

    public function testGetEmail()
    {
        $this->assertEquals('erickskrauch@ely.by', $this->createModel()->getEmail());
        $this->assertNull($this->createModelWithoutEmail()->getEmail());
    }

    public function testGetRegisteredAt()
    {
        $registeredAt = $this->createModel()->getRegisteredAt();
        $this->assertInstanceOf(\DateTime::class, $registeredAt);
        $this->assertEquals(1470566470, $registeredAt->getTimestamp());
    }

    public function testGetProfileLink()
    {
        $this->assertEquals('http://ely.by/u1', $this->createModel()->getProfileLink());
    }

    public function testGetPreferredLanguage()
    {
        $this->assertEquals('be', $this->createModel()->getPreferredLanguage());
    }

    public function testGetSkinUrl()
    {
        $this->assertEquals('http://skinsystem.ely.by/skins/ErickSkrauch.png', $this->createModel()->getSkinUrl());
    }

    public function testToArray()
    {
        $array = $this->createModel()->toArray();
        $this->assertTrue(is_array($array));
        $this->assertEquals(1, $array['id']);
        $this->assertEquals('ffc8fdc9-5824-509e-8a57-c99b940fb996', $array['uuid']);
        $this->assertEquals('ErickSkrauch', $array['username']);
        $this->assertEquals('erickskrauch@ely.by', $array['email']);
        $this->assertEquals(1470566470, $array['registeredAt']);
        $this->assertEquals('http://ely.by/u1', $array['profileLink']);
        $this->assertEquals('http://skinsystem.ely.by/skins/ErickSkrauch.png', $array['skinUrl']);

        $array = $this->createModelWithoutEmail()->toArray();
        $this->assertArrayNotHasKey('email', $array);
    }

    private function createModelWithoutEmail()
    {
        $params = $this->getAllResponseParams();
        unset($params['email']);

        return new ResourceOwner($params);
    }

    private function createModel()
    {
        return new ResourceOwner($this->getAllResponseParams());
    }

    private function getAllResponseParams()
    {
        return json_decode(file_get_contents(__DIR__ . '/data/identity-info-response.json'), true);
    }
}
