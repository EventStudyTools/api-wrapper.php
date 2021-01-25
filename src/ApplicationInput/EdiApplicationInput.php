<?php

namespace EventStudyTools\ApiWrapper\ApplicationInput;

/**
 * Class EdiApiInput
 * @package EventStudyTools\ApiWrapper\ApplicationInput
 */
class EdiApplicationInput extends AbstractApplicationInput implements ApplicationInputInterface
{
    /** @var string */
    const TEXT_DATA_FILE = 'text_data';

    /**
     * CataApiInput constructor.
     */
    public function __construct()
    {
        $this->setResultFileType(self::RESULT_FILE_TYPE_ODS);

        $this->setApplicationKey('edi');
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
    public function setTextDataSource($type, $hash = '')
    {
        $this->initDataSource(self::TEXT_DATA_FILE, $type, $hash);
    }
} 