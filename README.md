# Guardian

[![GitHub license](https://img.shields.io/github/license/mathieu-bour/guardian?style=flat-square)](https://github.com/mathieu-bour/guardian/blob/master/LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/mathieu-bour/guardian?style=flat-square)](https://packagist.org/packages/mathieu-bour/guardian)
[![Packagist](https://img.shields.io/packagist/dt/mathieu-bour/guardian?style=flat-square)](https://packagist.org/packages/mathieu-bour/guardian)
[![GitHub issues](https://img.shields.io/github/issues/mathieu-bour/guardian?style=flat-square)](https://github.com/mathieu-bour/guardian/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/mathieu-bour/guardian?style=flat-square)](https://github.com/mathieu-bour/guardian/pulls)
[![Codecov](https://img.shields.io/codecov/c/gh/mathieu-bour/guardian?style=flat-square)](https://codecov.io/gh/mathieu-bour/guardian)

Highly configurable JSON Web Token implementation for Laravel and Lumen.
It exposes an additional authentication `guardian` driver, which can be used like the standard `session` or `token`
drivers.


## Credits
- JWT implementation by [`web-token`](https://github.com/web-token/jwt-framework)


## Installation
Simply add Guardian to your project dependencies.

```bash
composer require mathieu-bour/guardian
```

### Laravel
Publish the default Guardian configuration:

```bash
php artisan vendor:publish --provider="Windy\Guardian\GuardianServiceProvider"
```

### Lumen
Copy the default Guardian configuration from `vendor/mathieu-bour/guardian/config/guardian.php` to
`config/guardian.php`.
Then, add the provider to your `bootstrap/app.php` and load the configuration with:

```php
$app->configure('guardian');

$app->register(Windy\Guardian\GuardianServiceProvider::class);
```

### Setup
