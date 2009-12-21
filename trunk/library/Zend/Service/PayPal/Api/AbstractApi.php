<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
abstract class Zend_Service_PayPal_Api_AbstractApi
{
    /**
     * The adapter used to send requests.
     * 
     * @var Zend_Service_PayPal_Adapter_AbstractAdapter
     */
    protected $_adapter;

    /**
     * the current fields setup for the request. 
     * 
     * @var array
     */
    protected $_fields;

    protected abstract $_methodName;
    
    /**
     * Constructor.
     * 
     * @param Zend_Service_PayPal_Adapter_AbstractAdapter $adapter 
     * 
     */
    public function __construct( Zend_Service_PayPal_Adapter_AbstractAdapter
    $adapter )
    {
        $this->_adapter = $adapter;
        $this->init();
    }

    public function init()
    {}

    public abstract function execute();

    /**
     * Sends the request using the Adapter, and returns the response.
     * 
     * @return Zend_Service_PayPal_Response
     */
    public function sendRequest()
    {
        $fields = $this->getFields();
        return $this->_adapter->callApi( $this->_methodName, $fields );
    }

    /**
     * set a single field.
     * 
     * @param string $name 
     * @param mixed $value 
     * 
     * @return void
     */
    public function setField( $name, $value )
    {
        $this->_fields[ strtoupper( $name ) ] = $value;
    }

    /**
     * Set fields.
     * 
     * @param array $fields 
     * @return void
     */
    public function setFields( array $fields )
    {
        foreach( $fields  as $name => $value ) {
            $this->setField( $name, $value );
        }
    }

    /**
     * Get all fields for this api method.
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $name 
     * 
     * @return TODO
     */
    public function getField( $name )
    {
        return @$this->_fields[ strtoupper( $name ) ]; 
    }

    public function __get( $name ) 
    {
        return $this->getField( $name );
    }

    public function __set( $name, $value )
    {
        $this->setField( $name, $value );
    }
}
