<?php
namespace Ely\OAuth2\Client;

use Ely\OAuth2\Client\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * @method ResourceOwner getResourceOwner(AccessToken $token)
 */
class Provider extends AbstractProvider {
    use BearerAuthorizationTrait;

    const REDIRECT_URI_STATIC_PAGE = 'static_page';
    const REDIRECT_URI_STATIC_PAGE_WITH_CODE = 'static_page_with_code';

    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl() {
        return 'https://account.ely.by/oauth2/v1/' . $this->clientId;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params) {
        return 'https://account.ely.by/api/oauth2/v1/token';
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return 'https://account.ely.by/api/account/v1/info';
    }

    /**
     * @inheritdoc
     */
    protected function getAuthorizationParameters(array $options) {
        $params = parent::getAuthorizationParameters($options);
        // client_id applied to base url
        // approval_prompt not supported
        unset($params['client_id'], $params['approval_prompt']);

        return $params;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultScopes() {
        return [
            'account_info',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data) {
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $message = isset($data['message']) ? $data['message'] : $response->getReasonPhrase();
            throw new IdentityProviderException($message, $statusCode, $response);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array       $response
     * @param AccessToken $token
     * @return ResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token) {
        return new ResourceOwner($response);
    }

}
