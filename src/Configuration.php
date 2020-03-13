<?php

namespace Dash;

class Configuration
{
    /**
     * @var string $clientID
     */
    private $clientID;

    /**
     * @var string $clientSecret
     */
    private $clientSecret;

    /**
     * @var string $companyCode
     */
    private $companyCode;

    /**
     * Configuration constructor.
     * @param string $clientID
     * @param string $clientSecret
     * @param string $companyCode
     */
    public function __construct($clientID, $clientSecret, $companyCode)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->companyCode = $companyCode;
    }

    /**
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getCompanyCode()
    {
        return $this->companyCode;
    }
}