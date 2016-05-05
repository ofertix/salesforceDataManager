<?php

namespace SalesForceDataManager;

class ClientFactory
{

    public $username;
    public $password;
    public $wdsl;
    public function __construct(array $config)
    {
        $this->wdsl = (isset($config['wdsl'])) ?
            $config['wdsl'] : $this->getDefaultWDSL();
        $this->username = (isset($config['username'])) ? $config['username'] : false;
        $this->password = (isset($config['password']) && isset($config['token']))
            ? $config['password'] . $config['token'] : false;
    }

    public function getDefaultWDSL()
    {

        $reflector = new \ReflectionClass('\SforcePartnerClient');
        $dirWDSL = dirname($reflector->getFileName());

        return $dirWDSL . DIRECTORY_SEPARATOR . 'partner.wsdl.xml';
    }

    /**
     * @return \SforcePartnerClient
     */
    public function getSoapClient()
    {
        $soapClient = new \SforcePartnerClient;
        $soapClient->createConnection($this->wdsl);
        $soapClient->login($this->username, $this->password);
        return $soapClient;
    }

    /**
     * @return \BulkApiClient
     */
    public function getBulkApiClient()
    {
        $soapclient = $this->getSoapClient();
        return new BulkApiClient(
            $soapclient->getLocation(),
            $soapclient->getSessionId()
        );
    }


}
