# Easy curl

## Installation

Github:

```bash
git clone https://github.com/Mateodioev/request
cd request
composer install
```

Composer:

```bash
composer require mateodioev/request
```

## Usage

```php
require 'path/to/vendor/autoload.php';

use Mateodioev\Request\Request;

# Start request
$req = new Request;
$req->Init($url, $curlopt_options); // $curlopt_options is opcional

$req->setMethod('GET');  // GET, HEAD, POST, PUT, DELETE, PATCH


# Start request static
$req = Request::http_method_name($url, $curlopt_options);

# Run request
$res = $req->Run($endpoint); // $endpoint is optional
echo $res
```

## Request Response
```php
$req = Request::GET('https://netotf.space/v1/api');
$res = $req->Run('/http-cat/200');
$res->toJson(true); // Parse body responde to json
// Headers info
$res->getHeaderRequest('Host')[0];
$res->getHeaderResponse('x-author')[0];

$res->setDebug(true); // Print all request info
echo $res // Print 
```
![example output](https://i.imgur.com/9gCymkL.png)
