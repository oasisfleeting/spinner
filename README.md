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

You should then be good to go and be able to access the spinner using the following static interface:

```php
// Optional seeding of a page to maintain content integrity for that page
// This means only one version will show for a specific page
Spinner::setSeedPageName();

// Set your content
Spinner::setContent('{color|size} is your {shirt|hat}');

// Return spun content
return Spinner::spin();
```
