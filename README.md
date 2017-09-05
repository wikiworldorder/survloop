
# WikiWorldOrder/SurvLoop

[![Laravel](https://img.shields.io/badge/Laravel-5.3-orange.svg?style=flat-square)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

SurvLoop is a Laravel-based engine for websites dominated by the collection and publication of open data. 
This is a database design and survey generation system, though it will increasingly be a flexible tool to solve many 
web-based problems.

It is currently in continued, heavy development, with much happening here in early 2017, almost ready to go live. 
I plan to provide more documentation in the coming weeks. Thank you for your interest and patience!

This was originally developed to build the 
<a href="https://github.com/flexyourrights/openpolice" target="_blank">Open Police</a> system. 
So until the SurvLoop installation processes automates everything, plus the bell & whistle options, 
please check out the Open Police package for an heavy example of how to extend SurvLoop for your custom needs. 
(Lighter examples coming online soon!-)

The upcoming Open Police web app is the best live <b>beta demo</b> of the engine's end results, 
and feedback on that project and the SurvLoop user experience can be  via the end of the submission process:<br />
<a href="http://openpolicereport.org/test" target="_blank">http://openpolicereport.org/test</a><br />
The resulting database designed using the engine, as well as the branching tree which specifies the user's experience: 
<a href="http://openpolicereport.org/db/OP" target="_blank">/db/OP</a><br />
<a href="http://openpolicereport.org/tree/complaint" target="_blank">/tree/complaint</a><br />
Among other methods, the resulting data can also be provided as 
XML included an automatically generated schema, eg.<br />
<a href="http://openpolicereport.org/complaint-xml-schema" target="_blank">/complaint-xml-schema</a><br />
<a href="http://openpolicereport.org/complaint-xml-example" target="_blank">/complaint-xml-example</a><br />
<a href="http://openpolicereport.org/complaint-xml-all" target="_blank">/complaint-xml-all</a>

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Documentation](#documentation)
* [Roadmap](#roadmap)
* [Change Logs](#change-logs)
* [Contribution Guidelines](#contribution-guidelines)


# <a name="requirements"></a>Requirements

* php: >=5.6.4
* <a href="https://packagist.org/packages/laravel/framework" target="_blank">laravel/framework</a>: 5.3.*
* <a href="https://packagist.org/packages/matthiasmullie/minify" target="_blank">matthiasmullie/minify</a>: 1.3.*

# <a name="getting-started"></a>Getting Started

Here are instructions if you are new to Laravel, or just want step-by-step instructions on how to install its 
development environment, Homestead: 
<a href="http://wikiworldorder.org/2016/11/26/coding-with-laravel-installing-homestead-on-a-mac/" target="_blank">
    WikiWorldOrder.org/2016/11/26/coding-with-laravel-installing-homestead-on-a-mac/</a>.

* Install Laravel's default user authentication, notifications, and SurvLoop:

```
$ php artisan make:auth
$ php artisan vendor:publish --tag=laravel-notifications
```

* Update `composer.json` to add requirements and an easier SurvLoop reference:

```
$ nano composer.json
```

```
...
"require": {
	...
    "wikiworldorder/survloop": "0.*",
	...
},
...
"autoload": {
	...
	"psr-4": {
		...
		"SurvLoop\\": "vendor/wikiworldorder/survloop/src/",
	}
	...
},
...
```

```
$ composer update
```

* Add the package to your application service providers in `config/app.php`.

```php
...
    'name' => 'SurvLoop',
...
'providers' => [
	...
	SurvLoop\SurvLoopServiceProvider::class,
	...
],
...
'aliases' => [
	...
	'SurvLoop'	=> 'WikiWorldOrder\SurvLoop\SurvLoopFacade',
	...
],
...
```

* Swap out the SurvLoop user model in `config/auth.php`.

```php
...
'model' => App\Models\User::class,
...
```

* Update composer, publish the package migrations, etc...

```
$ php artisan vendor:publish --force
$ php artisan migrate
$ composer dump-autoload
$ php artisan db:seed --class=SurvLoopSeeder
```

* For now, to apply database design changes to the same installation you are working in, depending on your server, 
you might also need something like this...

```
$ chown -R www-data:33 app/Models
$ chown -R www-data:33 database
```

# <a name="documentation"></a>Documentation

Once installed, documentation of this system's database design can be found at /dashboard/db/all . This system's user 
experience design for data entry can be found at /dashboard/tree/map?all=1&alt=1 
or publicly visible links like those above.


# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release (**1.0**). It's my first time building on Laravel, or GitHub. So sorry.

* [ ] Correct all issues needed for minimum viable product, and launch initial beta sites powered by SurvLoop.
* [ ] Database design and user experience admin tools to be generated by SurvLoop itself. 
* [ ] Code commenting, learning and adopting more community norms.
* [ ] Finish migrating all raw queries to use Laravel's process.
* [ ] Adding tests.

# <a name="change-logs"></a>Change Logs


# <a name="contribution-guidelines"></a>Contribution Guidelines

Please help educate me on best practices for sharing code in this community.
Please report any issue you find in the issues page.
