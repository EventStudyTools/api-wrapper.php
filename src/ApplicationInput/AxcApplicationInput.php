<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class ArcApplicationInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
abstract class AxcApplicationInput extends AbstractApplicationInput implements ApplicationInputInterface
{
    /** @var string */
    const FIRM_DATA_FILE = 'firm_data';

    /** @var string */
    const MARKET_DATA_FILE = 'market_data';

    /** @var string */
    const REQUEST_DATA_FILE = 'request_file';

    /** @var string */
    const RETURN_TYPE_LOG = 'log';

    /** @var string */
    const RETURN_TYPE_SIMPLE = 'simple';


    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * @param string $returnType
     */
    public function setReturnType($returnType)
    {
        $this->set('parameters', 'return_type', $returnType);
    }

    /**
     * @param string $resultFileType
     */
    public function setResultFileType($resultFileType)
    {
        $this->set('parameters', 'result_file_type', $resultFileType);
    }

    /**
     * @param string $nonTradingDays
     */
    public function setNonTradingDays($nonTradingDays)
    {
        $this->set('parameters', 'non_trading_days', $nonTradingDays);
    }

    /**
     * @param string $benchMarkModel
     */
    public function setBenchmarkModel($benchMarkModel)
    {
        $this->set('parameters', 'benchmark_model', $benchMarkModel);
    }

    /**
     * @param string $regressionMethod
     */
    public function setRegressionMethod($regressionMethod)
    {
        $this->set('parameters', 'regression_method', $regressionMethod);
    }

    /**
     * @param array $testStatistics
     */
    public function setTestStatistics($testStatistics)
    {
        if (!empty($testStatistics)) {
            foreach ($testStatistics as $value) {
                $this->parameters->test_statistics[] = $value;
            }
        }
    }

    /**
     * @param string $type data source type
     * @param string $hash MD5 hash of data for validation purposes
     */
    public function initRequestDataSource($type, $hash = '')
    {
        $this->initDataSource(self::REQUEST_DATA_FILE, $type, $hash);
    }

    /**
     * @param string $type data source type
     * @param string $hash MD5 hash of data for validation purposes
     */
    public function initMarketDataSource($type, $hash = '')
    {
        $this->initDataSource(self::MARKET_DATA_FILE, $type, $hash);
    }

    /**
     * @param string $type data source type
     * @param string $hash MD5 hash of data for validation purposes
     */
    public function initFirmDataSource($type, $hash = '')
    {
        $this->initDataSource(self::FIRM_DATA_FILE, $type, $hash);
    }
}