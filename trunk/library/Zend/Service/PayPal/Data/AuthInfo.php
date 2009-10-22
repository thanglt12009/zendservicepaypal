<?php

/**
 * 
 * 
 * @todo support the "subject" option as defined in the documentation, when making
 * 	the request on behalf of another user
 *
 */
class Zend_Service_PayPal_Data_AuthInfo
{
    
    protected $_username;
    
    protected $_password;
    
    protected $_signature;
    
    public function __construct( $username = null, $password = null, $signature = null )
    {
        $this->_username  = $username;
        $this->_password  = $password;
        $this->_signature = $signature; 
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
}