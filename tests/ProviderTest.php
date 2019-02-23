<?php
/** @noinspection PhpUnhandledExceptionInspection */
namespace Ely\OAuth2\Client\Test;

use Ely\OAuth2\Client\Exception\IdentityProviderException;
use Ely\OAuth2\Client\Provider;
use Ely\OAuth2\Client\ResourceOwner;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase {

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    protected function setUp() {
        $this->provider = new Provider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
        $this->mockHandler = new MockHandler();
        $client = new Client(['handler' => HandlerStack::create($this->mockHandler)]);
        $this->provider->setHttpClient($client);
    }

    public function testGetResourceOwnerDetailsUrl() {
        $url = $this->provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => 'mock_token']));
        $this->assertSame('/api/account/v1/info', parse_url($url, PHP_URL_PATH));
    }

    public function testGetAuthorizationUrl() {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertSame('/oauth2/v1/mock_client_id', $uri['path']);
        $this->assertArrayNotHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayNotHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes() {
        $options = ['scope' => ['minecraft_server_session', 'account_info']];
        $url = $this->provider->getAuthorizationUrl($options);
        $this->assertContains('scope=minecraft_server_session%2Caccount_info', $url);
    }

    public function testGetBaseAccessTokenUrl() {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $this->assertSame('/api/oauth2/v1/token', parse_url($url, PHP_URL_PATH));
    }

    public function testGetAccessToken() {
        $this->mockHandler->append(new Response(200, ['content-type' => 'json'], $this->getAccessTokenResponse()));

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertSame('mock_access_token', $token->getToken());
        $this->assertNotNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    public function testExceptionThrownWhenErrorObjectReceived() {
        $this->mockHandler->append(new Response(418, ['content-type' => 'json'], json_encode([
            'name' => 'Some error happened',
            'message' => 'Some exception message',
            'status' => 418,
            'code' => 0,
        ])));

        $this->setExpectedException(IdentityProviderException::class, 'Some exception message');

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownOnIncorrectContentType() {
        $this->mockHandler->append(new Response(502, ['content-type' => 'text/html; charset=UTF-8'], 'html content'));

        $this->setExpectedException(IdentityProviderException::class, 'Bad Gateway');

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResourceOwner() {
        $this->mockHandler->append(new Response(200, ['content-type' => 'json'], $this->getAccessTokenResponse()));
        $this->mockHandler->append(new Response(200, ['content-type' => 'json'], json_encode([
            'id' => 1,
            'uuid' => 'ffc8fdc9-5824-509e-8a57-c99b940fb996',
            'username' => 'ErickSkrauch',
            'registeredAt' => 1470566470,
            'profileLink' => 'http://ely.by/u1',
            'preferredLanguage' => 'be',
            'email' => 'erickskrauch@ely.by',
        ])));

        /** @var AccessToken $token */
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertInstanceOf(ResourceOwner::class, $user);
        $this->assertSame(0, $this->mockHandler->count());
    }

    private function getAccessTokenResponse() {
        return json_encode([
            'access_token' => 'mock_access_token',
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]);
    }

}
