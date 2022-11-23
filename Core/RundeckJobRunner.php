<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Core;

class RundeckJobRunner
{
    const RUNDECK_DOMAIN = 'scalecommerce.cloud';
    const JOB_ENDPOPINT = 'api/41/job/';

    protected $apiUrl = '';
    protected $authToken = '';

    public function __construct(array $credentials)
    {
        $this->apiUrl = 'https://'
            . ($credentials['rundeckSubdomain'] ?: 'www') . '.'
            . self::RUNDECK_DOMAIN . '/'
            . self::JOB_ENDPOPINT
            . $credentials['rundeckJobId']
            . '/run';
        $this->authToken = $credentials['authToken'];
    }

    /**
     * Run rundeck job
     *
     * @param array|null $parameter
     * @return string
     */
    public function runJob(?array $parameter = null): string
    {
        $argString = [];

        if (!empty($parameter)) {
            $argString = ['argString' => ''];

            foreach ($parameter as $key => $value) {
                $argString['argString'] .= ' -' . $key . ' ' . $value;
            }

            $argString['argString'] = ltrim($argString['argString']);
        }

        return $this->callCurlUrl(
            $this->apiUrl,
            [
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $argString + [
                        'authtoken' => $this->authToken,
                    ],
            ]
        );
    }

    /**
     * Checks if cache clear can be executed
     *
     * @return bool
     */
    protected function checkCredentials(): bool
    {
        return strpos($this->apiUrl, 'job//run') === false // No job id set up
            && !empty($this->authToken); // No auth token set up
    }

    /**
     * @param string $url
     * @param array $options
     * @return string
     */
    protected function callCurlUrl(string $url, array $options = []): string
    {
        if (!$this->checkCredentials()) {
            return 'check credentials!';
        }

        $curlHandler = curl_init();
        curl_setopt_array(
            $curlHandler,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
            ] + $options
        );
        $response = curl_exec($curlHandler);
        curl_close($curlHandler);

        return (string) $response;
    }
}
