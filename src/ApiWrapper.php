<?php
namespace EventStudyTools\ApiWrapper;

use EventStudyTools\ApiWrapper\ApplicationInput\ApplicationInputInterface;
use EventStudyTools\ApiWrapper\Exception\ApiSemanticException;

class ApiWrapper
{
    /**
     *  Http content type header
     */
    const CONTENT_TYPE_HEADER = 'Content-Type:';

    /**
     *  Http Content-Type header value
     */
    const CONTENT_TYPE_JSON = 'application/json';

    /**
     * Http Content-Type header value
     */
    const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';

    /**
     *  X-Customer-Key header
     */
    const CUSTOMER_KEY = 'X-Customer-Key:';

    /**
     * X-Task-Key header
     */
    const TASK_KEY_HEADER = 'X-Task-Key:';

    /** @var  string */
    protected $apiServerUrl;

    /** @var  string */
    protected $token;

    /** @var string */
    protected $maxChunkSize;

    /** @var string */
    protected $apiKey;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * ApiWrapper constructor.
     * @param $apiServerUrl
     * @param $apiKey
     * @param int $maxChunkSize
     *
     * 41943040b = 40M
     */
    public function __construct($apiServerUrl, $apiKey, $maxChunkSize = 41943040)
    {

        $this->token = '';
        $this->apiServerUrl = $apiServerUrl;
        $this->maxChunkSize = $maxChunkSize;
        $this->apiKey = $apiKey;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function authentication()
    {
        $response = $this->_request('/task/create', 'POST',
            [
                self::CONTENT_TYPE_HEADER . self::CONTENT_TYPE_JSON,
                self::CUSTOMER_KEY . $this->apiKey,
            ]);

        if (!$response['result'] === false && !preg_match('~<html~is', $response['result'])) {
            $temp = json_decode($response['result']);

            if (isset($temp->token) && !empty($temp->token)) {
                $this->token = $temp->token;
            }
        }

        return !empty($this->token);
    }

    /**
     * @param ApplicationInputInterface $input
     * @return mixed
     * @throws \Exception
     */
    public function configureTask(ApplicationInputInterface $input)
    {

        if (empty($this->apiServerUrl) || empty($this->token) || !is_object($input)) {
            throw new \Exception(__METHOD__ . ': required parameters aren\'t set');
        }

        $json = $input->toJson();

        $response = $this->_request('/task/conf', 'POST',
            [
                self::CONTENT_TYPE_HEADER . self::CONTENT_TYPE_JSON,
                self::TASK_KEY_HEADER . $this->token,
            ],
            $json
        );

        if ($response['result'] === false ||
            preg_match('~<html~is', $response['result']) ||
            !preg_match('~^true$~is', $response['result'])
        ) {

            throw new \Exception(__METHOD__ . ': configuration error');
        }

        return json_decode($response['result']);
    }

    /**
     * @param string $fileKey
     * @param string $fileName
     * @param int $partNumber
     * @throws \Exception
     */
    public function uploadFile($fileKey, $fileName, $partNumber = 0)
    {

        if (empty($this->apiServerUrl) || empty($this->token) || empty($fileKey) || empty($fileName)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        if (!($fd = fopen($fileName, 'r'))) {
            throw new \Exception(__METHOD__ . ': cannot read file ' . $fileName);
        }

        /*
         * +1000 is a dirty hack. I don't know why splitfile is set to write 41943040 but filesize yields 41943055
         * @todo understand why it happens and fix on more smart manner
         */

        if (filesize($fileName) > $this->maxChunkSize + 1000) {
            fclose($fd);

            $files = array();

            try {
                $files = $this->splitFile($fileName);

                foreach ($files as $key => $value) {
                    $this->uploadFile($fileKey, $value, $key);
                }
            } catch (\Exception $ex) {
                $this->deleteFileParts($files);
                throw new \Exception(__METHOD__ . ': cannot split file ' . $fileName);
            }

            /*
             * Clean resources
             */
            $this->deleteFileParts($files);

            return;
        }

        $ch = curl_init($this->apiServerUrl . "/task/content/$fileKey/" . (int)$partNumber);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                self::CONTENT_TYPE_HEADER . self::CONTENT_TYPE_OCTET_STREAM,
                self::TASK_KEY_HEADER . $this->token,
            )
        );

        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fd);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        fclose($fd);

        if ($result === false || preg_match('~<html~is', $result)) {
            throw new \Exception(__METHOD__ . ': file $fileKey upload error');
        }
    }

    /**
     * @param $parts
     */
    protected function deleteFileParts($parts)
    {

        if (!empty($parts)) {
            foreach ($parts as $file) {
                unlink($file);
            }
        }
    }

    /**
     * @param $fileName
     * @return array
     * @throws \Exception
     */
    protected function splitFile($fileName)
    {

        $i = 0;
        $partFileNames = array();

        $handle = fopen($fileName, 'r');

        while (!feof($handle)) {
            $buffer = '';
            $i++;
            $partFileName = $fileName . '.part' . $i;
            $partHandle = fopen($partFileName, 'w');

            if (!$partHandle) {
                fclose($handle);
                fclose($partHandle);
                throw new \Exception('Cannot write file part $partFileName');
            }

            while (strlen($buffer) < $this->maxChunkSize && !feof($handle)) {
                $buffer .= fgets($handle);
            }

            if (!fputs($partHandle, $buffer)) {
                fclose($handle);
                fclose($partHandle);
                throw new \Exception('Cannot write to file ($partFileName)');
            }

            $partFileNames[] = $partFileName;
            fclose($partHandle);
        }

        fclose($handle);

        return $partFileNames;
    }

    /**
     * @return mixed
     * @throws ApiSemanticException
     * @throws \Exception
     */
    public function commitData()
    {
        if (empty($this->apiServerUrl) || empty($this->token)) {
            throw new ApiSemanticException(__METHOD__ . ': Configuration validation error');
        }

        $response = $this->_request('/task/commit', 'POST',
            [
                self::CONTENT_TYPE_HEADER . self::CONTENT_TYPE_JSON,
                self::TASK_KEY_HEADER . $this->token,
            ]);

        $result = $response['result'];

        if ($result === 'false' || preg_match('~<html~is', $result)) {
            throw new ApiSemanticException(__METHOD__ . ': application data are incorrect');
        }

        $decodedResponse =  json_decode($result);

        if (isset($decodedResponse->error)) {
            throw new ApiSemanticException($decodedResponse->error);
        }

        return $decodedResponse;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function processTask()
    {
        if (empty($this->apiServerUrl) || empty($this->token)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        $response = $this->_request('/task/process', 'POST',
            [
                self::CONTENT_TYPE_HEADER . self::CONTENT_TYPE_JSON,
                self::TASK_KEY_HEADER . $this->token,
            ]);

        $result = $response['result'];
        /*
         * Handle any unusual output
         */
        if ($result === 'false' || preg_match('~<html~is', $result)) {
            throw new \Exception(__METHOD__ . ': Application launch error');
        }

        /*
         * Handle standard error output
         */
        if ($response['code'] != 200) {
            $result = json_decode($result);

            if (isset($result->error)) {
                throw new ApiSemanticException($result->error);
            } else {
                throw new ApiSemanticException('Application launch error');
            }
        }

        return json_decode($result);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getApiVersion()
    {
        $version = '';
        $response = $this->_request('/version', 'GET');

        if ($response['code'] == 200) {
            $result = json_decode($response['result']);
            if (isset($result->version)) {
                $version = $result->version;
            }
        }

        return $version;
    }

    /**
     * @param $apiMethodName
     * @param $httpMethod
     * @param array $httpHeaders
     * @param string $params
     * @return array
     * @throws \Exception
     */
    private function _request($apiMethodName, $httpMethod, $httpHeaders = [], $params = '')
    {

        $response = [];

        $ch = curl_init($this->apiServerUrl . $apiMethodName);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        if ($httpMethod === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response['result'] = curl_exec($ch);
        $response['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response['error'] = curl_error($ch);
        curl_close($ch);

        if (!empty($response['error'])) {
            throw new \Exception($response['error']);
        }

        return $response;
    }
}