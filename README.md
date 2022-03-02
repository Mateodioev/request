# Easy curl

## Installation

Github:

```bash
git clone https://github.com/Mateodioev/request
cd db
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

# GET
$res = Request::Get('https://httpbin.org/get');
# POST
$res = Request::Post('https://httpbin.org/post');

# Format
$method = 'GET'; // GET, POST, PUT, DELETE, etc.
$res = Request::$method($url, $headers_array, $postfield_data);

# Methods
Request::Init();
Request::AddOpts();
Request::AddOpt();
Request::addHeaders();
Request::addBody();
Request::setMethod();
Request::Close();
Request::Run();
Request::Create();
Request::Download();
```