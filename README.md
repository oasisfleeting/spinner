Article Spinner Package
============

Laravel Facade and Service Provider for CodeGreenCreative\Spinner

Installation
---

To use, simply install the package via Composer and then add the following to your app/config/app.php to the service providers array:

```php
'CodeGreenCreative\Spinner\SpinnerServiceProvider',
```

Then add to the aliases array the following:
```php
'Spinner' => 'CodeGreenCreative\Spinner\Facade',
```

