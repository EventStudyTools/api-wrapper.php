<?php
namespace EventStudyTools\ApiWrapper;

use EventStudyTools\ApiWrapper\ApplicationInputInterface;

class ApiWrapper
{
    /** @var  string */
    protected $apiServerUrl;

    /** @var  string */
    protected $token;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $apiEndpoint
     */
    function __construct($apiEndpoint) {
        $this->token = '';
        $this->apiServerUrl = $apiEndpoint;
    }

    /**
     * @param string $apiKey
     * @param bool $debug
     * @return bool
     */
    function authentication($apiKey, $debug=false)
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
        curl_close($ch);

        if ($debug) {
            var_dump($result);
            exit;
        }

        if (!$result === false && !preg_match("~<html~is", $result)) {
            $temp = json_decode($result);

            if (isset($temp->token) && !empty($temp->token)) {
                $this->token = $temp->token;
            }
        }

        return !empty($this->token);
    }

    /**
     * @param ApplicationInputInterface $input
     * @param bool $debug
     * @return mixed
     * @throws \Exception
     */
    function configureTask(ApplicationInputInterface $input, $debug=false) {

        if (empty($this->apiServerUrl) || empty($this->token) || !is_object($input)) {
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
        curl_close($ch);

        if ($debug) {
            var_dump($result);
            exit;
        }

        if ($result === false || preg_match("~<html~is", $result) || !preg_match("~^true$~is", $result)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        return json_decode($result);
    }

    /**
     * @param string $fileKey
     * @param string $fileName
     * @param int $partNumber
     * @throws \Exception
     */
    function uploadFile($fileKey, $fileName, $partNumber=0)
    {

        if (empty($this->apiServerUrl) || empty($this->token) || empty($fileKey) || empty($fileName)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }

        if ( !($fd = fopen($fileName, 'r') ) ) {
            throw new \Exception(__METHOD__ . ': cannot read file ' . $fileName);
        }

        /*
         * @todo define class field for maxChunkSize
         */
        $maxChunkSize = 40 * 1024 *1024;

        /*
         * +1000 is a dirty hack. I don't know why splitfile is set to write 41943040 but filesize yields 41943055
         * @todo understand why it happens and fix on more smart manner
         */
        if ( filesize($fileName) > $maxChunkSize +1000) {
            fclose($fd);

            $files =array();
            try {
                $files = $this->splitFile($fileName, $maxChunkSize);
                foreach($files as $key => $value) {
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
        curl_close($ch);

        fclose($fd);

        if ($result === false || preg_match("~<html~is", $result)) {
            throw new \Exception(__METHOD__ . ": file $fileKey upload error");
        }
    }

    /**
     * @param $parts
     */
    protected function deleteFileParts($parts) {
        if (!empty($parts)) {
            foreach($parts as $file) {
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
    protected function splitFile($fileName, $maxChunkSize) {

        $i=0;
        $partFileNames = array();

        $handle = fopen($fileName, 'r');

        while (!feof ($handle)) {
            $buffer = '';
            $i++;
            $partFileName =$fileName . '.part' . $i;
            $partHandle = fopen($partFileName, "w");

            if (!$partHandle) {
                fclose($handle);
                fclose($partHandle);
                throw new \Exception(" Cannot write file part $partFileName");
            }

            while(strlen($buffer)  < $maxChunkSize && !feof($handle)) {
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

        fclose ($handle);

        return $partFileNames;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    function commitData()
    {
        if (empty($this->apiServerUrl) || empty($this->token)) {
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
        curl_close($ch);

        if ($result === false || preg_match("~<html~is", $result) || !preg_match("~^\{\"log\":~is", $result)) {
            throw new \Exception(__METHOD__ . ": application data are incorrect");
        }

        return json_decode($result);
    }

    /**
     * @param bool $debug if want see API output more than get results
     * @throws \Exception
     * @return mixed
     */
    function processTask($debug = false)
    {
        if (empty($this->apiServerUrl) || empty($this->token)) {
            throw new \Exception(__METHOD__ . ': configuration error');
        }


        $ch = curl_init($this->apiServerUrl . "/task/process");
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

        if ($debug) {
            var_dump($result);
            exit;
        }

       /*
        * Handle any unusual output
        */
        if ($result === false || preg_match("~<html~is", $result) || !preg_match("~^\{\"log\":~is", $result)) {
            throw new \Exception(__METHOD__ . ": Application launch error");
        }

        /*
         * Handle standard error output
         */
        if ($httpcode != 200) {
            $result = json_decode($result);

            if (isset($result->error)) {
                throw new \Exception($result->error);
            } else {
                throw new \Exception("Application launch error");
            }
        }

        return json_decode($result);
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
}