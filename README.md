# Bark SDK

This is PHP SDK for Bark.
Bark is a world-leading services marketplace with over 5m customers in 8 countries around the world.

Visit the [Bark website](https://www.bark.com/) for more information.

---
## Installation

You can install the package via composer:

```bash
composer require mralston/bark-sdk
```
## Basic Usage

```php

use Mralston\Bark\Client;
use Mralston\Bark\Contact;
use Mralston\Bark\Flow;

// Log in
$client = new Client(
    $client_id,
    $secret,
    $apiEndpoint
);

// Fetch all barks
foreach ($client->listBarks() as $bark) {
    dump($bark);
}
```

## Fluent API

Many of the objects exposed by the API support method chaining.

## Laravel

**Configuration**

In Laravel, you can publish the config file with:
```bash
php artisan vendor:publish --provider="Mralston\Bark\BarkServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'client_id' => env('BARK_CLIENT_ID'),
    'secret' => env('BARK_SECRET'),
    'api_endpoint' => env('BARK_API_ENDPOINT'),
];
```

Configure the environment variables with your client ID, secret.

```dotenv
BARK_CLIENT_ID=
BARK_SECRET=
```

**Dependency Injection**

In addition to the method chaining described in the fluent API section above, the Laravel integration takes care of
authentication automatically. All you need to do is grab an instance of the client from the container and start using it.

You can use dependency injection to get a pre-authenticated instance of the client:

```php
use Illuminate\Http\Request;
use Mralston\Bark\Client;

class MyController
{
    public function create(Request $request, Client $client)
    {
        // Create new contact using POST data
        $barks = $client->listBarks(),
        )
    }
}
```

Alternatively, you can resolve an instance of the client from the container:

```php
use Mralston\Bark\Client;

$client = app(Client::class);
```

**Facade**

In true Laravel tradition, you can also use a facade (along with method chaining, of course!).

```php
use Mralston\Bark\Facades\Bark;

$barks = Bark::listBarks();
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Matt Ralston](https://github.com/mralston)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
