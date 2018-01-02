<?php


namespace Azure;


use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

abstract class AzureClient
{
    const API_BASE_URL = 'https://management.azure.com/';
    const VM_API_VERSION = '2016-04-30-preview';
    const LOCATIONS_API_VERSION = '2016-06-01';
    const RESOURCEGROUPS_API_VERSION = '2017-05-10';
    const NETWORK_INTERFACE_API_VERSION = '2017-10-01';

    /**
     * @var string
     */
    protected $subscriptionId;

    /**
     * @var string
     */
    private $subscriptionUrl;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $authenticationToken;

    /**
     * @var integer
     */
    protected $guzzleVersion;

    /**
     * AzureVMClient constructor.
     *
     * @param string $tenantId
     * @param string $subscriptionId
     * @param string $appId
     * @param string $password
     */
    public function __construct($tenantId, $subscriptionId, $appId, $password)
    {
        $this->guzzleVersion = (version_compare(Client::VERSION, 6) === 1 ) ? 6 : 5;
        $this->authenticationToken = $this->getToken($tenantId, $appId, $password);
        $this->subscriptionId = $subscriptionId;
        $this->subscriptionUrl = self::API_BASE_URL.'subscriptions/'.$subscriptionId.'/';
    }

    /**
     * GET Wrapper
     *
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    public function get($url)
    {
        $url = ltrim($url, '/');
        $client = $this->getClient();
        try{
            $r = $client->get($url);
            $body = $this->parseResponse($r);
            return $body;
        }
        catch (\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new \Exception($error['error']['message']);
        }
    }

    /**
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    public function delete($url)
    {
        $url = ltrim($url, '/');
        $client = $this->getClient();
        try{
            $r = $client->delete($url);
            $body = $this->parseResponse($r);
            return $body;
        }
        catch (\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new \Exception($error['error']['message']);
        }
    }

    /**
     * PUT Wrapper
     *
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    public function put($url, $params = [])
    {
        $url = ltrim($url, '/');
        $client = $this->getClient();
        $options['json'] = $params;

        try{
            $r = $client->put($url, $options);
            $body = $this->parseResponse($r);
            return $body;
        }
        catch (\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new \Exception($error['error']['message']);
        }
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function post($url, $params = [])
    {
        $url = ltrim($url, '/');
        $client = $this->getClient();
        $options['json'] = $params;
        try{
            $r = $client->post($url, $options);
            $body = $this->parseResponse($r) ?: 'ok';
            return $body;
        }
        catch (\Exception $e)
        {
            $error = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new \Exception($error['error']['message']);
        }
    }

    /**
     * Fetch token from Microsoft Azure.
     * Check out README.md on how to obtain these credentials.
     *
     * @param string $tenantId
     * @param string $appId
     * @param string $password
     * @return string
     * @throws \Exception
     */
    private function getToken($tenantId, $appId, $password)
    {
        $client = new Client();
        $body = [
            'resource' => 'https://management.core.windows.net/',
            'client_id' => $appId,
            'client_secret' => $password,
            'grant_type' => 'client_credentials',
        ];

        switch($this->guzzleVersion) {
            case 5:
                $r = $client->post(
                    "https://login.windows.net/".$tenantId."/oauth2/token",
                    ['body' => $body]
                );
                break;
            default:
                $r = $client->request(
                    'POST',
                    "https://login.windows.net/".$tenantId."/oauth2/token",
                    ['form_params' => $body]
                );
        }

        $body = $this->parseResponse($r);
        if (isset($body->token_type, $body->access_token) && $body->token_type === 'Bearer') {
            return $body->access_token;
        }

        throw new \Exception('Unable to fetch Access Token for Azure.');
    }

    /**
     * Helper to get body object from response.
     *
     * @param ResponseInterface $r
     * @return mixed
     * @throws \Exception
     */
    protected function parseResponse(ResponseInterface $r)
    {
        if (stripos($r->getStatusCode(), '20') === 0) {
            return json_decode($r->getBody()->getContents());
        }

        throw new \Exception($r->getBody()->getContents());
    }

    /**
     * Get/create Guzzle Client for Azure API.
     *
     * @return Client
     */
    protected function getClient()
    {
        if (!isset($this->client)) {
            $config = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->authenticationToken,
                    'Content-Type' => 'application/json',
                ],
            ];

            switch ($this->guzzleVersion) {
                case 5:
                    $config = [
                        'base_url' => $this->subscriptionUrl,
                        'defaults' => $config,
                    ];
                    break;
                case 6:
                    $config['base_uri'] = $this->subscriptionUrl;
                    break;
            }

            $this->client = new Client($config);
        }

        return $this->client;
    }
}