# Ely.by Provider for OAuth 2.0 Client

This package provides Ely.by OAuth 2.0 support for the PHP League's
[OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](ico-build-status)](link-build-status)

## Installation

To install, use composer:

```
composer require ely/oauth2-client
```

## Usage

Usage is the same as The League's OAuth client, using `\Ely\OAuth2\Client\Provider` as the provider. You can find
more information in [League repository README](https://github.com/thephpleague/oauth2-client#authorization-code-grant).

You can get your own `clientId` and `clientSecret` at [Ely.by Account OAuth2 registration page](#).

```php
<?php
$provider = new \Ely\OAuth2\Client\Provider([
    'clientId'     => '{elyby-client-id}',
    'clientSecret' => '{elyby-client-secret}',
    'redirectUri'  => 'http://example.com/callback-uri',
]);
```

We suggest to put this provider object into service locator for access it at any time or mock for testing.
In code below we think, that `$provider` contains our provider object.

### Authorization Code Flow

First of all, you must generate redirect user to route, which will set state session value and redirect user to Ely.by
authorization page. This can be done by such code, placed into controller:

```php
<?php
$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();
header('Location: ' . $authUrl);
exit();
```

Note, that `getAuthorizationUrl()` takes as argument array of overriding parameters. For example, if you want request
additional scopes and change app description, then you must pass `scope` and `description` keys with needed values:

```php
<?php
$authUrl = $provider->getAuthorizationUrl([
    'scope' => ['account_info', 'account_email'],
    'description' => 'My super application!',
]);
```

After user finish authentication and authorization on Ely.by Account site, he will be redirected back, on `redirectUri`,
that you specified in Provider configuration. Inside redirectUri handler you must check for errors and state matches.
If all checks passed normal, then try to exchange received `auth_code` to `access_token`. This can be done by code
like below:

```php
<?php
if (isset($_GET['error'])) {
    echo 'Oh no! The error ' . $_GET['error'] . ' with message ' . $_GET['message'];
} elseif (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    unset($_SESSION['oauth2state']);
    echo 'Invalid state value.';
} else {
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(new \League\OAuth2\Client\Grant\AuthorizationCode(), [
        'code' => $_GET['code'],
    ]);

    // Optional: Now you have a token you can look up a users account data
    try {
        // We got an access token, let's now get the user's details
        $account = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $account->getUsername());
    } catch (\Ely\OAuth2\Client\Exception\IdentityProviderException $e) {
        // Failed to get user details
        echo 'Cannot get user account identity. The error is ' . $e->getMessage();
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Refreshing a Token

Refresh tokens are only provided to applications which request offline access. You can specify offline access by
setting the `scope` option on authorization url generating:

```php
<?php
$authUrl = $provider->getAuthorizationUrl([
    'scope' => ['account_info', 'account_email', 'offline_access'],
]);
```

It is important to note that the refresh token is only returned on the first request after this it will be null.
You should securely store the refresh token when it is returned:

```php
<?php
$token = $provider->getAccessToken('authorization_code', [
    'code' => $code
]);

// persist the token in a database
$refreshToken = $token->getRefreshToken();
```

Now you have everything you need to refresh an access token using a refresh token:

```php
<?php
$token = $provider->getAccessToken(new League\OAuth2\Client\Grant\RefreshToken(), [
    'refresh_token' => $refreshToken,
]);
```

## Testing

```bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

This package was designed and developed within the [Ely.by](http://ely.by) project team. We also thank all the
[contributors](link-contributors) for their help.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ely/oauth2-client.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ely/oauth2-client.svg?style=flat-square
[ico-build-status]: https://img.shields.io/travis/elyby/league-oauth2-ely/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ely/oauth2-client
[link-contributors]: ../../contributors
[link-downloads]: https://packagist.org/packages/ely/php-tempmailbuster
[link-build-status]: https://travis-ci.org/elyby/league-oauth2-ely
