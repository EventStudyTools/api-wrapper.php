<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class AvycApplicationInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
class AvycApplicationInput extends AxcApplicationInput
{
    /** @var string */
    const RETURN_TYPE_LOG = 'log';

    /** @var string */
    const RETURN_TYPE_SIMPLE = 'simple';

    /** @var string */
    const BENCHMARK_MODEL_GARCH = 'garch';

    /** @var string */
    const NON_TRADING_DAYS_LATER = 'later';

    /** @var string */
    const NON_TRADING_DAYS_EARLIER = 'earlier';

    /** @var string */
    const NON_TRADING_DAYS_KEEP = 'keep';

    /** @var string */
    const NON_TRADING_DAYS_SKIP = 'skip';

    /** @var string */
    const REGRESSION_METHOD_OLS = 'ols';

    /**
     * AVyCApplicationInput constructor.
     */
    public function __construct()
    {
        $this->setApplicationKey('avyc');

        /*
         *  Set default values
         */
        $this->setReturnType(self::RETURN_TYPE_LOG);
        $this->setResultFileType(self::RESULT_FILE_TYPE_ODS);
        $this->setNonTradingDays(self::NON_TRADING_DAYS_KEEP);
        $this->setBenchmarkModel(self::BENCHMARK_MODEL_GARCH);
        $this->setRegressionMethod(self::REGRESSION_METHOD_OLS);
    }
}