# EventStudyTools (EST) API PHP Wrapper

This software library provides the capability to easily deploy the EST API.

* More detailed documentation about available applications can be found [here](http://wwww.eventtudytools.com)
* The full API documentation is presented [here](http://wwww.eventtudytools.com/API-ARC)

##Example of an Abnormal Returns Calculatior (ARC) launch

```php
define('API_URL', 'http://api.est.dev');
define('API_KEY', 'key1234567890');
define('STATUS_DONE', 3);
define('STATUS_ERROR', 4);

require './../vendor/autoload.php';

$parameters = [
  'result_file_type' => 'xls',
  'benchmark_model' =>  'mm',
  'return_type' =>  'log',
  'non_trading_days' =>  'later',
  'test_statistics' => [
      'art' =>  '1',
      'cart' =>  '1',
      'aart' =>  '1',
      'caart' =>  '1',
      'abhart' =>  '1',
      'aarptlz' =>  '1',
      'caarptlz' =>  '1',
      'aaraptlz' =>  '1',
      'caaraptlz' =>  '1',
      'aarbmpz' =>  '1',
      'caarbmpz' =>  '1',
      'aarabmpz' =>  '1',
      'caarabmpz' =>  '1',
      'aarskewadjt' =>  '1',
      'caarskewadjt' =>  '1',
      'abharskewadjt' =>  '1',
      'aarrankz' =>  '1',
      'caarrankz' =>  '1',
      'aargrankt' =>  '1',
      'caargrankt' =>  '1',
      'aargrankz' =>  '1',
      'caargrankz' =>  '1',
      'aargsignz' =>  '1',
      'caargsignz' =>  '1',
      'aarcdat' =>  '1',
      'aarjackknivet' =>  '1',
   ],
  'datasources' => [
      'request_file' =>  'csv',
      'firm_data' =>  'csv',
      'market_data' =>  'csv'
  ]
];

$api = new \EventStudyTools\ApiWrapper\ApiWrapper(API_URL);

if ($api->authentication(API_KEY)) {
    $api->configureTask(new \EventStudyTools\ApiWrapper\ApplicationInput\ArcApplicationInput($parameters));
    $api->uploadFile('firm_data', './firm_data.csv');
    $api->uploadFile('market_data', './market_data.csv');
    $api->uploadFile('request_file', './request_file.csv');
    $api->commitData();

    do {
        sleep(15);
        $status = $api->getTaskStatus();
    } while (!in_array($status, array(STATUS_DONE, STATUS_ERROR)));

    switch ($status) {
        case STATUS_DONE:
            $results = $api->getTaskResults();
            var_dump($results);
            break;

        case STATUS_ERROR:
            echo "Task \"" . $api->getToken() . "\" was terminated with error\n";
            break;
    }
}
```

##Example of a Computer-Aided Text Analysis (CATA) launch

```php
define('API_URL', 'http://api.est.dev');
define('API_KEY', 'key1234567890');
define('BASE_PATH', __DIR__);
define('STATUS_DONE', 3);
define('STATUS_ERROR', 4);

require BASE_PATH . '/../vendor/autoload.php';

$parameters = [
  'datasources' => [
      'text_data' =>  'csv_zip',
      'keywords_data' =>  'csv_zip'
  ]
];

$api = new \EventStudyTools\ApiWrapper\ApiWrapper(API_URL);

if ($api->authentication(API_KEY)) {
    $api->configureTask(new \EventStudyTools\ApiWrapper\ApplicationInput\CataApplicationInput($parameters));
    $api->uploadFile('text_data', './texts.csv.zip');
    $api->uploadFile('keywords_data', './dictionary.csv.zip');
    $api->commitData();

    do {
        sleep(15);
        $status = $api->getTaskStatus();
    } while (!in_array($status, array(STATUS_DONE, STATUS_ERROR)));

    switch ($status) {
        case STATUS_DONE:
            $results = $api->getTaskResults();
            var_dump($results);
            break;

        case STATUS_ERROR:
            echo "Task \"" . $api->getToken() . "\" was terminated with error\n";
            break;
    }
}
```