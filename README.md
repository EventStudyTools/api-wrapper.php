## EventStudyTools (EST) API PHP Wrapper

Events are at the heart of many phenomena that are of interest to scholars - from M&A to organizational behavior. Several research methods have been developed to empirically study events and event sequences. EventStudyTools helps you deploy these methods for your specific research question by providing a set of analytical research apps. You can use these apps right as-a-service from this website or deploy them through APIs or R-packages. The latter gives you the opportunity to make programmable use of the methods. Figure below illustrates our current set of analytical research apps.

![Research Apps for Event-Centric Research](https://www.eventstudytools.com/sites/default/files/pictures/Apps_Elements.png)

API supports the following research apps:
+ AVC - The abnormal volume calculator (AVC) reveals whether or not the analyzed events led to additional, abnormal trading volumes.
+ ARC - The basic Abnormal Return Calculator (bARC) allows you to perform basic event studies without much options to set parameters.		 
+ CATA - The keyword-based computer-aided text analyzer allows scanning for keywords in large amounts of texts. It was developed to support news analytics, the text analysis of corporate news streams.
+ AVyC - The abnormal volatility calculator yields whether or not the analyzed events produced statistically abnormal volatility.		
+ EDI - The Event Date Identifier (EDI) is a regular expression-based tool which identifies dates within texts, such as press releases. Dates of events or decisions represent major inputs for event studies. The EDI was designed to identify such dates within large amounts of press releases.	

This software library provides the capability to easily deploy the EST API.

* More detailed documentation about available applications can be found [here](https://www.eventstudytools.com)
* The full API documentation is presented [here](https://www.eventstudytools.com/API-ARC)


## Abnormal Returns Calculator (ARC)

Source files to try API you can download [here](https://www.eventstudytools.com/sites/all/themes/eventstudy_new/sample_data_aarc.zip)

#### Example of an Abnormal Returns Calculator (ARC) launch

```php
<?php
    define('API_URL', 'http://api.eventstudytools.com');
    define('API_KEY', '<API_KEY>');
    
    $api = new ApiWrapper(API_URL, API_KEY);
    
    if ($api->authentication()) {

        $appInput = new ArcApplicationInput();

        $appInput->setTestStatistics([
            ArcTestStatistics::AAR_T,
            ArcTestStatistics::AAR_GRANKT,
            ArcTestStatistics::CAAR_ABMPZ,
            ArcTestStatistics::AAR_RANKZ,
            ArcTestStatistics::AAR_SKEWADJT,
            ArcTestStatistics::CAAR_GRANKT,
            ArcTestStatistics::CAAR_SKEWADJT
        ]);

        $appInput->setEmail('test@example.org');
        $appInput->setLocale(ArcApplicationInput::LOCALE_EN);

        $appInput->initFirmDataSource(ArcApplicationInput::DATA_SOURCE_TYPE_CSV);
        $appInput->initMarketDataSource(ArcApplicationInput::DATA_SOURCE_TYPE_CSV);
        $appInput->initRequestDataSource(ArcApplicationInput::DATA_SOURCE_TYPE_CSV);

        $appInput->setResultFileType(ArcApplicationInput::RESULT_FILE_TYPE_XLS);

        $configurationResult = $api->configureTask($appInput);
        echo 'Configuration result:';
        var_dump($configurationResult);

        $api->uploadFile(ArcApplicationInput::REQUEST_DATA_FILE, './01_RequestFile.csv');
        $api->uploadFile(ArcApplicationInput::FIRM_DATA_FILE, './02_FirmData.csv');
        $api->uploadFile(ArcApplicationInput::MARKET_DATA_FILE, './03_MarketData.csv');        

        $api->commitData();

        $result = $api->processTask();

        echo 'Results:';
        var_dump($result);
    }
```

## CATA
The keyword-based computer-aided text analyzer (CATA) allows scanning for keywords in large amounts of texts. It was developed to support news analytics, the text analysis of corporate news streams.

Source files to try API you can download [here](https://www.eventstudytools.com/sites/all/themes/eventstudy_adapt/sample_data_cata.zip)

#### Example of he keyword-based computer-aided text analyzer (CATA)launch

```php
<?php

define('API_URL', 'http://api.eventstudytools.com');
define('API_KEY', '<API_KEY>');

$api = new ApiWrapper(API_URL, API_KEY);


if ($api->authentication()) {

    $appInput = new CataApiInput();

    $appInput->setEmail('test@example.org');
    $appInput->initKeywordsDataSource(CataApiInput::DATA_SOURCE_TYPE_XLS);
    $appInput->initTextDataSource(CataApiInput::DATA_SOURCE_TYPE_CSV);
    $appInput->setLocale(CataApiInput::LOCALE_EN);

    $configurationResult = $api->configureTask($appInput);
    echo 'Configuration result:';
    var_dump($configurationResult);

    $api->uploadFile(CataApiInput::TEXT_DATA_FILE, './1_Texts.csv');
    $api->uploadFile(CataApiInput::KEYWORDS_DATA_FILE, './2_Analysis Scheme.csv');

    $api->commitData();

    $result = $api->processTask();

    echo 'Results:';
    var_dump($result);
}

```