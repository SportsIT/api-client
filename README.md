# Dash - API Client Library
Library for interacting with the [Dash](https://www.dashplatform.com/) API documented [here](https://api.dashplatform.com/v1/docs).

## Installation
### Requirements
| Client Version | PHP Version Required |
|----------------|----------------------|
|        3       |        >= 7.2        |
|      <= 2      |        >= 5.6        |

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

See [Docs](docs/index.md).