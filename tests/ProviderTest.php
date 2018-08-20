<?php
namespace Ely\OAuth2\Client\Test;

use Ely\OAuth2\Client\Provider;
use Ely\OAuth2\Client\ResourceOwner;
use GuzzleHttp\ClientInterface;
use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;

class ProviderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Provider
     */
    protected $provider;

    protected function setUp() {
        $this->provider = new Provider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown() {
        m::close();
        parent::tearDown();
    }

    public function testGetResourceOwnerDetailsUrl() {
        $url = $this->provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => 'mock_token']));
        $uri = parse_url($url);
        $this->assertEquals('/api/account/v1/info', $uri['path']);
    }

    public function testGetAuthorizationUrl() {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('/oauth2/v1/mock_client_id', $uri['path']);
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

        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetBaseAccessTokenUrl() {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/api/oauth2/v1/token', $uri['path']);
    }

    public function testGetAccessToken() {
        /** @var m\Mock|ResponseInterface $response */
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($this->getAccessTokenResponse());
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        /** @var m\Mock|ClientInterface $client */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNotNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    /**
     * @expectedException \Ely\OAuth2\Client\Exception\IdentityProviderException
     * @expectedExceptionMessageRegExp /Exception message .+/
     */
    public function testExceptionThrownWhenErrorObjectReceived() {
        $name = 'Error ' . uniqid();
        $message = 'Exception message ' . uniqid();
        $status = mt_rand(400, 600);
        /** @var m\Mock|ResponseInterface $postResponse */
        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')->andReturn(json_encode([
            'name' => $name,
            'message' => $message,
            'status' => $status,
            'code' => 0,
        ]));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        /** @var m\Mock|ClientInterface $client */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @expectedException \Ely\OAuth2\Client\Exception\IdentityProviderException
     * @expectedExceptionMessage Bad Gateway
     */
    public function testExceptionThrownOnIncorrectContentType() {
        /** @var m\Mock|ResponseInterface $postResponse */
        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'text/html; charset=UTF-8']);
        $postResponse->shouldReceive('getBody')->andReturn('html content');
        $postResponse->shouldReceive('getStatusCode')->andReturn(502);
        $postResponse->shouldReceive('getReasonPhrase')->andReturn('Bad Gateway');

        /** @var m\Mock|ClientInterface $client */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testGetResourceOwner() {
        /** @var m\Mock|ResponseInterface $postResponse */
        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')->andReturn($this->getAccessTokenResponse());
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        /** @var m\Mock|ResponseInterface $userResponse */
        $userResponse = m::mock(ResponseInterface::class);
        $userResponse->shouldReceive('getBody')->andReturn(
            file_get_contents(__DIR__ . '/data/identity-info-response.json')
        );
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        /** @var m\Mock|ClientInterface $client */
        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertInstanceOf(ResourceOwner::class, $user);
    }

    private function getAccessTokenResponse() {
        return json_encode([
            'access_token' => 'mock_access_token',
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]);
    }

}
