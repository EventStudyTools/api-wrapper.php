# EventStudyTools (EST) API PHP Wrapper

This software library provides the capability to easily deploy the EST API.

* More detailed documentation about available applications can be found [here](http://wwww.eventtudytools.com)
* The full API documentation is presented [here](http://wwww.eventtudytools.com/API-ARC)

##Example of an Abnormal Returns Calculatior (ARC) launch

```php
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

$api = new EventStudyTools\ApiWrapper\ApiWrapper('arc', $apiEndpointUrl)));

if ($api->authentication($apiKey)) {
    $api->configureTask(new EventStudyTools\ApiWrapper\ApplicationInput\ArcApplicationInput($parameters));
    $api->uploadFile('firm_data', './firmData.csv'));
    $api->uploadFile('market_data', './marketData.csv'));
    $api->uploadFile('request_file', './requestFile.csv'));
    $api->commitData();
    $result = $api->processTask();
}
```

##Example of a Computer-Aided Text Analysis (CATA) launch

```php
$parameters = [
  'datasources' => [
      'text_data' =>  'csv_zip',
      'keywords_data' =>  'csv_zip'
  ]
];

$api = new EventStudyTools\ApiWrapper\ApiWrapper('cata', $apiEndpointUrl)));

if ($api->authentication($apiKey)) {
    $api->configureTask(new EventStudyTools\ApiWrapper\ApplicationInput\CataApplicationInput($parameters));
    $api->uploadFile('text_data', './texts.csv.zip');
    $api->uploadFile('keywords_data', './dictionary.csv.zip');
    $api->commitData();
    $result = $api->processTask();
}
```