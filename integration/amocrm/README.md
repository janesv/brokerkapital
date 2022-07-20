> # amoCRM API Client

> Convenient client for interaction with [amoCRM API](https://developers.amocrm.ru/rest_api/).

![amoCRM API Client](http://kamilsk.github.io/amoCRM/images/box.png) ![Octopus Laboriosus](http://kamilsk.github.io/amoCRM/images/octopus.png)

### Example of usage

#### Basic library work

```php
require 'path/to/amoCRM/API.php';

$app->register('query', array(
		'url' => 'https://example.amocrm.ru',
		'user' => 'user@example.com',
		'token' => 'd41d8cd98f00b204e9800998ecf8427e',
	))
	->setRuntimePath(__DIR__);

echo '<pre>';
$response = OctoLab\amoCRM\dao\Account::current();
var_dump(
	$response->getCode(),
	$response->getHeader(),
	$response->getBody()
);
exit;
```

#### CLI command examples

```
$ cd path/to/amoCRM/library
$ cli example --help
$ cli example account::current [--show_code=true] [--show_header] [--show_body]
```

### Required

* PHP 5.3.0 or greater;
* Account on [amoCRM](http://www.amocrm.com/) for work with API (free plan available).