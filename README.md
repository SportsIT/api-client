### Dash - API Client Library

_Additional documentation to follow._

## Installing Dash API Client

### With Composer
The recommended way to install Dash API Client is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Dash API Client:

```bash
php composer.phar require sportsit/api-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update Dash API Client using composer:

 ```bash
composer.phar update
 ```

## Usage
Creation of a client can be done as shown below:
 ```php
$config = new \Dash\Configuration($clientID, $clientSecret, $companyCode);
$client = new \Dash\Client($config);
```

The client is a simple wrapper around the Guzzle HTTP library and as such any Guzzle methods can be accessed on the client as well.
```php
$response = $client->get($uri, $options);
```

Making calls to the Dash API requires that you have a valid access token so make sure you authenticate with the API before making any other calls. Once you have an access token, it will automatically be added to all subsequent calls.
```php
$response = $client->authenticate()->get($uri, $options);
```
