### DASH Platform - API Client Library

_Additional documentation to follow._

## Installing DashApi

### With Composer
The recommended way to install DashApi is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of DashApi:

```bash
php composer.phar require sportsit/api-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update DashApi using composer:

 ```bash
composer.phar update
 ```

 ## Without Composer

 Why are you not using composer?
 Download contents of `/src` directory from the repo to a local directory in your project. An example of integration might look like
 ```<?php
 require 'path/to/DashApi/Client/';
