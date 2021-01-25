<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class ArcApplicationInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
class ArcApplicationInput extends AxcApplicationInput
{
    /** @var string */
    const RETURN_TYPE_LOG = 'log';

    /** @var string */
    const RETURN_TYPE_SIMPLE = 'simple';

    /** @var string */
    const BENCHMARK_MODEL_MM = 'mm';

    /** @var string */
    const BENCHMARK_MODEL_MM_SW = 'mm-sw';

    /** @var string */
    const BENCHMARK_MODEL_CPMAM = 'cpmam';

    /** @var string */
    const BENCHMARK_MODEL_MAM = 'mam';

    /** @var string */
    const BENCHMARK_MODEL_FF3FM = 'ff3fm';

    /** @var string */
    const BENCHMARK_MODEL_FFM4FM = 'ffm4fm';

    /** @var string */
    const BENCHMARK_MODEL_GARCH = 'garch';

    /** @var string */
    const BENCHMARK_MODEL_EGARCH = 'egarch';

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
     * ArcApplicationInput constructor.
     */
    public function __construct()
    {
        $this->setApplicationKey('arc');

        /*
         *  Set default values
         */
        $this->setReturnType(self::RETURN_TYPE_LOG);
        $this->setResultFileType(self::RESULT_FILE_TYPE_ODS);
        $this->setNonTradingDays(self::NON_TRADING_DAYS_KEEP);
        $this->setBenchmarkModel(self::BENCHMARK_MODEL_MM);
        $this->setRegressionMethod(self::REGRESSION_METHOD_OLS);
    }
}