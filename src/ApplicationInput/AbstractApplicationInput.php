<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class AbstractApplicationInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
abstract class AbstractApplicationInput
{
    /** @var string */
    const LOCALE_EN = 'en';

    /** @var string */
    const DATA_SOURCE_TYPE_CSV = 'csv';

    /** @var string */
    const DATA_SOURCE_TYPE_CSV_ZIP = 'csv_zip';

    /** @var string */
    const DATA_SOURCE_TYPE_XLS = 'xls';

    /** @var string */
    const DATA_SOURCE_TYPE_XLS_ZIP = 'xls_zip';

    /** @var string */
    const DATA_SOURCE_TYPE_XLSX = 'xlsx';

    /** @var string */
    const DATA_SOURCE_TYPE_XLSX_ZIP = 'xlsx_zip';

    /** @var string */
    const RESULT_FILE_TYPE_XLS = 'xls';

    /** @var string */
    const RESULT_FILE_TYPE_ODS = 'ods';

    /** @var string */
    const RESULT_FILE_TYPE_CSV = 'csv';

    /** @var string  */
    const RESULT_FILE_TYPE_XLSX = 'xlsx';

    /** @var \stdClass */
    public $application;

    /** @var \stdClass */
    public $parameters;

    /**
     * @param string $where
     * @param string $key
     * @param mixed $value
     */
    protected function set($where, $key, $value)
    {
        if (!empty($where) && !empty($key)) {

            if (!isset($this->$where)) {
                $this->$where = new \stdClass();
            }

            $this->$where->$key = $value;
        }
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->set('task', 'email', $email);
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->set('task', 'locale', $locale);
    }

    /**
     * @param string $key
     */
    protected function setApplicationKey($key)
    {
        $this->set('application', 'key', $key);
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $hash
     */
    protected function initDataSource($key, $type, $hash = '')
    {
        $dataSource = new \stdClass();
        $dataSource->key = $key;
        $dataSource->type = $type;
        $dataSource->hash = $hash;

        if (!isset($this->application->data_sources)) {
            $this->application->data_sources = [];
        }

        $itemIndex = null;
        foreach ($this->application->data_sources as $index => $data_source) {
            if ($data_source->key === $key) {
                $itemIndex = $index;
                break;
            }
        }

        if (null === $itemIndex) {
            $this->application->data_sources[] = $dataSource;
        } else {
            $this->application->data_sources[$itemIndex] = $dataSource;
        }
    }
}