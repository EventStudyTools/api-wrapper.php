<?php
namespace EventStudyTools\ApiWrapper;

use EventStudyTools\ApiWrapper\ApplicationInput\ApplicationInputInterface;

class ApiWrapper
{
    /** @var  string */
    protected $apiServerUrl;

    /** @var  string */
    protected $token;

    /**
     * @param $apiEndpoint
     */
    function __construct($apiEndpoint)
    {
        $this->token = '';
        $this->apiServerUrl = $apiEndpoint;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $apiKey
     * @param bool $debug
     * @throws \Exception
     * @return bool
     */
    function authentication($apiKey, $debug = false)
    {
        $ch = curl_init($this->apiServerUrl . "/task/create");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "X-Customer-Key: $apiKey",
            )
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($debug) {
            var_dump($result);
            exit;
        }

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__);

        if (!is_object($result) || empty($result->token)) {
            throw new \Exception(__METHOD__ . ': authentication failed');
        }

        $this->token = $result->token;

        return !empty($this->token);
    }

    /**
     * @param ApplicationInputInterface $input
     * @param bool $debug
     * @return mixed
     * @throws \Exception
     */
    function configureTask(ApplicationInputInterface $input, $debug = false)
    {
        if (empty($this->token) || !is_object($input)) {
            throw new \Exception(__METHOD__ . ": required parameters aren't set");
        }

        $ch = curl_init($this->apiServerUrl . '/task/conf');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "X-Task-Key: $this->token",
            )
        );

        $json = $input->toJson();

        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($debug) {
            var_dump($result);
            exit;
        }

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__);

        if ($result !== true) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        return true;
    }

    /**
     * @param string $fileKey
     * @param string $fileName
     * @param int $partNumber
     * @throws \Exception
     */
    function uploadFile($fileKey, $fileName, $partNumber = 0)
    {
        if (empty($this->token) || empty($fileKey) || empty($fileName)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        if (!($fd = fopen($fileName, 'r'))) {
            throw new \Exception(__METHOD__ . ': cannot read file ' . $fileName);
        }

        /*
         * @todo define class field for maxChunkSize
         */
        $maxChunkSize = 40 * 1024 * 1024;

        /*
         * +1000 is a dirty hack. I don't know why splitfile is set to write 41943040 but filesize yields 41943055
         * @todo understand why it happens and fix on more smart manner
         */
        if (filesize($fileName) > $maxChunkSize + 1000) {
            fclose($fd);

            $files = array();
            try {
                $files = $this->splitFile($fileName, $maxChunkSize);
                foreach ($files as $key => $value) {
                    $this->uploadFile($fileKey, $value, $key);
                }
            } catch (\Exception $ex) {
                $this->deleteFileParts($files);
                throw new \Exception(__METHOD__ . ": cannot split file " . $fileName);
            }

            /*
             * Clean resources
             */
            $this->deleteFileParts($files);

            return;
        }

        $ch = curl_init($this->apiServerUrl . "/task/content/$fileKey/" . (int)$partNumber);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/octet-stream",
                "X-Task-Key: $this->token",
            )
        );

        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fd);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        fclose($fd);

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__ . ' (' . $fileKey . ')');

        if ($result !== true) {
            throw new \Exception(__METHOD__ . ' (' . $fileKey . ')' . ': configuration error');
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
     * @param string $fileName
     * @param integer $maxChunkSize , bytes
     * @return array
     * @throws \Exception
     */
    protected function splitFile($fileName, $maxChunkSize)
    {
        $i = 0;
        $partFileNames = array();

        $handle = fopen($fileName, 'r');

        while (!feof($handle)) {
            $buffer = '';
            $i++;
            $partFileName = $fileName . '.part' . $i;
            $partHandle = fopen($partFileName, "w");

            if (!$partHandle) {
                fclose($handle);
                fclose($partHandle);
                throw new \Exception("Cannot write file part $partFileName");
            }

            while (strlen($buffer) < $maxChunkSize && !feof($handle)) {
                $buffer .= fgets($handle);
            }

            if (!fputs($partHandle, $buffer)) {
                fclose($handle);
                fclose($partHandle);
                throw new \Exception("Cannot write to file ($partFileName)");
            }

            $partFileNames[] = $partFileName;
            fclose($partHandle);
        }

        fclose($handle);

        return $partFileNames;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function commitData()
    {
        if (empty($this->token)) {
            throw new \Exception(__METHOD__ . ': Configuration validation error');
        }

        $ch = curl_init($this->apiServerUrl . "/task/commit");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "X-Task-Key: $this->token",
            )
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__);

        if (!is_object($result) || empty($result->log)) {
            throw new \Exception(__METHOD__ . ': response is invalid');
        }

        return $result;
    }

    /**
     * @param $exceptionOnError
     * @return integer
     * @throws \Exception
     */
    function getTaskStatus($exceptionOnError = false)
    {
        if (empty($this->token)) {
            throw new \Exception(__METHOD__ . ': Configuration validation error');
        }

        $ch = curl_init($this->apiServerUrl . "/task/status");

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "X-Task-Key: $this->token",
            )
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__, $exceptionOnError);

        return $result;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function getTaskResults()
    {
        if (empty($this->token)) {
            throw new \Exception(__METHOD__ . ': Configuration validation error');
        }

        $ch = curl_init($this->apiServerUrl . "/results/" . $this->getToken());

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
            )
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = $this->checkAndNormalizeResponse($result, $httpcode, __METHOD__);

        if (empty($result) || empty($result->results)) {
            throw new \Exception(__METHOD__ . ': result is empty');
        }

        return $result;
    }

    /**
     * @return string
     */
    function getApiVersion()
    {
        $ch = curl_init($this->apiServerUrl . "/version");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $version = '';
        if ($httpcode == 200) {
            $response = json_decode($result);
            if (isset($response->version)) {
                $version = $response->version;
            }
        }

        return $version;
    }

    /**
     * Normalize and check response
     * @param $response
     * @param $httpcode
     * @param $method
     * @param $exceptionOnError
     * @return mixed
     * @throws \Exception
     */
    protected function checkAndNormalizeResponse($response, $httpcode, $method, $exceptionOnError = true)
    {
        if ($response === false) {
            if ($exceptionOnError) {
                throw new \Exception($method . ': request to api failed');
            } else {
                return false;
            }
        }

        $response = json_decode($response);

        if ($httpcode != 200 || (is_object($response) && !empty($response->error))) {
            if ($exceptionOnError) {
                if (is_object($response) && !empty($response->error)) {
                    throw new \Exception($method . ': ' . $response->error);
                } else {
                    throw new \Exception($method . ': Application error');
                }
            } else {
                return false;
            }
        }

        return $response;
    }
}