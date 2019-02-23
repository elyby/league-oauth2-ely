<?php
namespace Ely\OAuth2\Client\Test;

use DateTime;
use Ely\OAuth2\Client\ResourceOwner;
use PHPUnit\Framework\TestCase;

class ResourceOwnerTest extends TestCase {

    public function testGetId() {
        $this->assertSame(1, $this->createModel()->getId());
    }

    public function testGetUuid() {
        $this->assertSame('ffc8fdc9-5824-509e-8a57-c99b940fb996', $this->createModel()->getUuid());
    }

    public function testGetUsername() {
        $this->assertSame('ErickSkrauch', $this->createModel()->getUsername());
    }

    public function testGetEmail() {
        $this->assertSame('erickskrauch@ely.by', $this->createModel()->getEmail());
        $this->assertNull($this->createModelWithoutEmail()->getEmail());
    }

    public function testGetRegisteredAt() {
        $registeredAt = $this->createModel()->getRegisteredAt();
        $this->assertInstanceOf(DateTime::class, $registeredAt);
        $this->assertSame(1470566470, $registeredAt->getTimestamp());
    }

    public function testGetProfileLink() {
        $this->assertSame('http://ely.by/u1', $this->createModel()->getProfileLink());
    }

    public function testGetPreferredLanguage() {
        $this->assertSame('be', $this->createModel()->getPreferredLanguage());
    }

    public function testGetSkinUrl() {
        $this->assertSame('http://skinsystem.ely.by/skins/ErickSkrauch.png', $this->createModel()->getSkinUrl());
    }

    public function testToArray() {
        $array = $this->createModel()->toArray();
        $this->assertInternalType('array', $array);
        $this->assertSame(1, $array['id']);
        $this->assertSame('ffc8fdc9-5824-509e-8a57-c99b940fb996', $array['uuid']);
        $this->assertSame('ErickSkrauch', $array['username']);
        $this->assertSame('erickskrauch@ely.by', $array['email']);
        $this->assertSame(1470566470, $array['registeredAt']);
        $this->assertSame('http://ely.by/u1', $array['profileLink']);
        $this->assertSame('http://skinsystem.ely.by/skins/ErickSkrauch.png', $array['skinUrl']);

        $array = $this->createModelWithoutEmail()->toArray();
        $this->assertArrayNotHasKey('email', $array);
    }

    private function createModelWithoutEmail() {
        $params = $this->getAllResponseParams();
        unset($params['email']);

        return new ResourceOwner($params);
    }

    private function createModel() {
        return new ResourceOwner($this->getAllResponseParams());
    }

    private function getAllResponseParams() {
        return [
            'id' => 1,
            'uuid' => 'ffc8fdc9-5824-509e-8a57-c99b940fb996',
            'username' => 'ErickSkrauch',
            'registeredAt' => 1470566470,
            'profileLink' => 'http://ely.by/u1',
            'preferredLanguage' => 'be',
            'email' => 'erickskrauch@ely.by',
        ];
    }

}
