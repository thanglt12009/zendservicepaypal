<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
abstract class Zend_Service_Paypal_Adapter_AbstractAdapter
{
    
    /**
     * API Username
     * @var string
     */
    protected $_username;

    /**
     * API password
     * 
     * @var string
     */
    protected $_password;

    /**
     * API Signature
     * 
     * @var strng
     */
    protected $_signature;

    /**
     * Logger
     * 
     * @var Zend_Log
     */
    protected $_log;

    /**
     * Constructor
     * 
     * @param array|Zend_Config $options   
     * @return void
     */
    public function __construct( $options = null )
    {
        if( is_array( $options ) ) {
            $this->setOptions( $options );
        } elseif ( $optinos instanceOf Zend_Config ) {
            $this->setConfig( $options );
        }

        $this->_setDefaultLog();
        $this->_setDefaultHttpClient();
    }

    /**
     * Attempts to execute an Api Method.  An attempt will be made to load a
     * Zend_Service_PayPal_Api_ class matching the method.  If a method is not
     * found, the PayPal API will be called directly.
     * 
     * @param string $method the API method to call
     * @param array $fields 
     * @return Zend_Service_PayPal_Response
     */
    protected function _executeApiMethod( $method, array $fields = array() )
    {
        $className = self::API_CLASS_PREFIX . ucfirst( $method );
        $classFound = false;

        try {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass( $className );
            $classFound = true;

        } catch ( Zend_Exception $e ) {
            $classFound = false;
        }
        
        if( $classFound ) {
            $class = new $className( $this );
            $class->setFields( $fields );
            return $class->execute();
        } else {
            //attempt to just call the API directly.
            return $this->callApi( $method, $fields );
        }
    }

    /**
     * Executes a method. All paypal calls are made through this method.  
     * 
     * @param string $method the API method to call
     * @param array $fields 
     * 
     * @return Zend_Service_PayPal_Response
     */
    public function callApi( $method, array $fields )
    {
        
        $this->_validateCredentials();
        $auth = array(
            'username'  => $this->_username,
            'password'  => $this->_password,
            'signature' => $this->_signature
        );
        $fields = $auth + $fields;

        $this->_callApi( $method, $fields );
    }

    protected abstract _callApi( $method, array $fields )
    {}

    /**
     * Username setter
     * 
     * @param string $username 
     * @return Zend_Service_PayPal_AdapterAbstract
     */
    public function setUsername( $username )
    {
        $this->_username = $username;
        return $this;
    }

    /**
     * Password setter.
     *
     * @param string $password 
     * @return Zend_Service_PayPal_Adapter_AbstractAdapter
     */
    public function setPassword( $password )
    {
        $this->_password = $password;
        return $this;
    }

    /**
     * Signature setter.
     *
     * @param string $signature 
     * @return Zend_Service_PayPal_Adapter_AbstractAdapter
     */
    public function setSignature( $signature )
    {
        $this->_signature = $signature;
        return $this;
    }
    
    public function getUsername()
    {
        return $this->_username;
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function getSignature()
    {
        return $this->_signature;
    }

    /**
     * Set adapter state from array of options
     * 
     * @param array $options 
     * @return Zend_Service_PayPal_AdapterAbstract
     */
    public function setOptions( array $options ) 
    {
        if( isset( $options['username'] ) ) {
            $this->setUsername( $options['username'] );
            unset( $options['username'];
        }

        if( isset( $options['password'] ) ) {
            $this->setPassword( $options['password'];
            unset( $options['password'] );
        }

        if( isset( $options['signature'] ) ) {
            $this->setPassword( $options['signature'] );
        }

        return $this;
    }
   
    /**
     * Get the HTTP client to be used for this service
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }
    
    /**
     * Set the HTTP client to be used for this service
     *
     * @param Zend_Http_Client $client
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_httpClient = $client;
    }
  
    /**
     * Sets options based on a config object
     * 
     * @param Zend_Config $config 
     * @return Zend_Service_PayPal_AdapterAbstract
     */
    public function setConfig( Zend_Config $config )
    {
        return $this->setOptions( $config->toArray() );
    }

    /**
     * If not currently set, a default logger will be created. 
     * 
     * @return void
     */
    protected function _setDefaultLog()
    {
        if( ! $this->_log instanceOf Zend_Log ) {
            require_once 'Zend/Log.php';
            require_once 'Zend/Log/Writer/Null.php';
            $writer = new Zend_Log_Writer_Null();
            $this->setLog( new Zend_Log( $writer ) );
        }
    }

    /**
     * If not currently set, a default logger will be created. 
     * 
     * @return void
     */
    protected function _setDefaultHttpClient()
    {
        if( ! $this->_httpClient instanceOf Zend_Http_Client ) {
            require_once 'Zend/Http/Client.php';
            $this->setHttpClient( new Zend_Http_Client() );
        }
    }

    /**
     * Attempts to call find an API class with the given
     * method name, and calls that API classes execute()
     * method if found.
     * 
     * @param mixed $method 
     * 
     * @return TODO
     */
    public function __call( $method, $args ) 
    {
        $methodFields = array();
        if( is_array( $args[0] ) ) {
            $methodFields = $args[0];
        }
        return $this->_executeApiMethod( $method, $methodFields );
    }
}
