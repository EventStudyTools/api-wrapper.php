<?php
namespace EventStudyTools\ApiWrapper\ApplicationInput;

use stdClass;

class ArcApplicationInput extends AbstractApplicationInput implements ApplicationInputInterface {
    /**
     * @param array $parameters
     */
    public function __construct($parameters) {

        /*
         * Task
         */
        $this->set('task', $parameters, 'email');
        $this->setWithDefault('task', $parameters, 'locale', 'en');

        /*
         * Application
         */
        $this->setWithDefault('application', $parameters, 'key', 'arc');

        $request = new stdClass();
        $request->key = 'request_file';
        $request->type = $parameters['datasources']['request_file'];

        $firm = new stdClass();
        $firm->key = 'firm_data';
        $firm->type = $parameters['datasources']['firm_data'];

        $market = new stdClass();
        $market->key = 'market_data';
        $market->type = $parameters['datasources']['market_data'];

        $this->application->data_sources = array(
            $request, $firm, $market
        );

        /*
         * Parameters
         */
        $this->set('parameters', $parameters, 'return_type');
        $this->set('parameters', $parameters, 'result_file_type');
        $this->set('parameters', $parameters, 'non_trading_days');
        $this->set('parameters', $parameters, 'benchmark_model');
        $this->set('parameters', $parameters, 'regression_method');

        if (!empty($parameters['test_statistics'])) {
            foreach($parameters['test_statistics'] as $key=>$value) {
                if (1==$value) {
                    $this->parameters->test_statistics[] = $key;
                }
            }
        }
    }

    public function toJson() {
        return json_encode($this);
    }
} 