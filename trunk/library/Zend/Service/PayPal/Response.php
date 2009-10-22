<?php
class Zend_Service_PayPal_Response
{
    protected $_data = array();
    
    /**
     * Create a new response object from response string
     *
     * @param string $data
     */
    public function __construct ($response) {}
    
    /**
     * Check whether the request was successfull
     *
     * @return boolean
     */
    public function isSuccess() {}
    
    /**
     * Check whether the request failed
     *
     * @return boolean
     */
    public function isFailure() {}
    
    /**
     * Check whether the request produced warnings
     *
     * Note: Successful requests might produce warnings as well
     *
     * @return boolean
     */
    public function hasWarnings() {}
    
    /**
     * Get the value of a response parameter
     *
     * @param  string $name
     * @return string|null Value or null if not set
     */
    public function getValue($name) {}
    
    /**
     * Magic wrapper around getValue()
     *
     * Unlike getValue(), accessing parameters through __get() will throw an exception if the
     * parameter was not set in the response.
     *
     * @param  string $name Key
     * @return string
     */
    public function __get($name) {}
} 
