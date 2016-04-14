<?php

namespace ApplicationInput;

use stdClass;

abstract class AbstractApplicationInput {

    /** @var  stdClass */
    public $application;

    /** @var  array */
    public $parameters;

    /**
     * @param string $where
     * @param array $parameters
     * @param string $key
     * @param mixed $defaultValue
     */
    protected function setWithDefault($where, $parameters, $key, $defaultValue) {
        if (isset($parameters[$key]) && !empty($parameters[$key])) {
            $this->$where->$key = $parameters[$key];
        } elseif(empty($parameters[$key])) {

            if (!isset($this->$where)) {
                $this->$where = new stdClass();
            }

            $this->$where->$key = $defaultValue;
        }
    }

    /**
     * @param string $where
     * @param array $parameters
     * @param string $key
     */
    protected function set($where, $parameters, $key) {
        if (isset($parameters[$key]) && !empty($parameters[$key])) {

            if (!isset($this->$where)) {
                $this->$where = new stdClass();
            }

            $this->$where->$key = $parameters[$key];
        }
    }
} 