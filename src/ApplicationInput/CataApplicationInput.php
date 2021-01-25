<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class CataApiInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
class CataApplicationInput extends AbstractApplicationInput implements ApplicationInputInterface
{
    /** @var string */
    const TEXT_DATA_FILE = 'text_data';

    /** @var string */
    const KEYWORDS_DATA_FILE = 'keywords_data';

    /**
     * CataApiInput constructor.
     */
    public function __construct()
    {
        $this->setResultFileType(self::RESULT_FILE_TYPE_ODS);

        $this->setApplicationKey('cata');
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * @param string $resultFileType
     */
    public function setResultFileType($resultFileType)
    {

        $this->set('parameters', 'result_file_type', $resultFileType);
    }

    /**
     * @param string $type data source type
     * @param string $hash MD5 hash of data for validation purposes
     */
    public function initTextDataSource($type, $hash = '')
    {
        $this->initDataSource(self::TEXT_DATA_FILE, $type, $hash);
    }

    /**
     * @param string $type data source type
     * @param string $hash MD5 hash of data for validation purposes
     */
    public function initKeywordsDataSource($type, $hash = '')
    {
        $this->initDataSource(self::KEYWORDS_DATA_FILE, $type, $hash);
    }
} 