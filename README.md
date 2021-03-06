# LogAnalyzer
LogAnalyzer is PHP library that analyze log files.(ex: apache access log, ltsv) This library displays log data in a table format organized by dimensions. You can specify dimensions as log data keys or calculate programmable. If you want more detailed analysis, you can analyze recursively.

[![Latest Stable Version](https://poser.pugx.org/nazonohito51/log-analyzer/version)](https://packagist.org/packages/nazonohito51/log-analyzer)

## Usage
### Simple usage.

```php
use LogAnalyzer\CollectionBuilder;

$collection = (new CollectionBuilder())->addApacheLog('path/to/apache1.log')->addApacheLog('path/to/apache2.log')->build();

$collection->dimension('request')->addColumn('host')->addColumn('HeaderUserAgent')->table()->display();

/*
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| request                         | Count | host                          | HeaderUserAgent                                                                                                 |
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| GET /users/1/articles HTTP/1.0  | 1     | 133.130.35.34                 | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| POST /users/1/articles HTTP/1.0 | 1     | 133.130.35.34                 | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| GET / HTTP/1.1                  | 3     | [23.96.184.214, 93.158.152.5] | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| GET /robots.txt HTTP/1.1        | 1     | 93.158.152.5                  | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)                                                |
| POST /users/2/profile HTTP/1.0  | 1     | 133.130.35.34                 | Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_5) AppleWebKit/5312 (KHTML, like Gecko) Chrome/14.0.894.0 Safari/5312 |
| POST /users/3/articles HTTP/1.0 | 1     | 133.130.35.35                 | Mozilla/5.0 (X11; Linuxi686; rv:7.0) Gecko/20101231 Firefox/3.6                                                 |
| GET /login HTTP/1.1             | 1     | 66.249.79.82                  | Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/3.0)                                        |
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
*/
```

### Calculate dimension value
You can calculate dimension value by closure.

```php
use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\Items\ItemInterface;

$collection = (new CollectionBuilder())->addApacheLog('path/to/apache1.log')->addApacheLog('path/to/apache2.log')->build();

$collection->dimension('request', function ($value) {
    // Remove HTTP method and HTTP version.
    if (preg_match('/[^\s]+\s([^\s]+)\sHTTP/', $value, $matches)) {
        return $matches[1];
    }
    return null;
})->addColumn('host')->addColumn('HeaderUserAgent')->table()->display();

/*
+-------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| request           | Count | host                          | HeaderUserAgent                                                                                                 |
+-------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| /users/1/articles | 2     | 133.130.35.34                 | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| /                 | 3     | [23.96.184.214, 93.158.152.5] | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| /robots.txt       | 1     | 93.158.152.5                  | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)                                                |
| /users/2/profile  | 1     | 133.130.35.34                 | Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_5) AppleWebKit/5312 (KHTML, like Gecko) Chrome/14.0.894.0 Safari/5312 |
| /users/3/articles | 1     | 133.130.35.35                 | Mozilla/5.0 (X11; Linuxi686; rv:7.0) Gecko/20101231 Firefox/3.6                                                 |
| /login            | 1     | 66.249.79.82                  | Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/3.0)                                        |
+-------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
*/
```

### Analyze recursively
If you want to analyze more detail, you can get Collection object recursively from the View object.

```php
use LogAnalyzer\CollectionBuilder;
use LogAnalyzer\Items\ItemInterface;

$collection = (new CollectionBuilder())->addApacheLog('path/to/apache1.log')->addApacheLog('path/to/apache2.log')->build();

$view = $collection->dimension('request');
$view->addColumn('host')->addColumn('HeaderUserAgent')->table()->display();

/*
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| request                         | Count | host                          | HeaderUserAgent                                                                                                 |
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
| GET /users/1/articles HTTP/1.0  | 1     | 133.130.35.34                 | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| POST /users/1/articles HTTP/1.0 | 1     | 133.130.35.34                 | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| GET / HTTP/1.1                  | 3     | [23.96.184.214, 93.158.152.5] | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350                     |
| GET /robots.txt HTTP/1.1        | 1     | 93.158.152.5                  | Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)                                                |
| POST /users/2/profile HTTP/1.0  | 1     | 133.130.35.34                 | Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_5) AppleWebKit/5312 (KHTML, like Gecko) Chrome/14.0.894.0 Safari/5312 |
| POST /users/3/articles HTTP/1.0 | 1     | 133.130.35.35                 | Mozilla/5.0 (X11; Linuxi686; rv:7.0) Gecko/20101231 Firefox/3.6                                                 |
| GET /login HTTP/1.1             | 1     | 66.249.79.82                  | Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/3.0)                                        |
+---------------------------------+-------+-------------------------------+-----------------------------------------------------------------------------------------------------------------+
*/

$collection = $view->getCollection('GET / HTTP/1.1');
$collection->dimension('host')->addColumn('HeaderUserAgent')->table()->display();

/*
+---------------+-------+---------------------------------------------------------------------------------------------+
| host          | Count | HeaderUserAgent                                                                             |
+---------------+-------+---------------------------------------------------------------------------------------------+
| 23.96.184.214 | 1     | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350 |
| 93.158.152.5  | 2     | Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350 |
+---------------+-------+---------------------------------------------------------------------------------------------+
*/
```
