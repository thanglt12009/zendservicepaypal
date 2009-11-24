<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class Zend_Service_PayPal
{
    const API_MODE_NVP  = 'Nvp';
    const API_MODE_SOAP = 'Soap';
    
    const PAYMENT_ACTION_SALE          = 'Sale';
    const PAYMENT_ACTION_AUTHORIZATION = 'Authorization';
    
    const REFUND_TYPE_OTHER            = 'Other';
    const REFUND_TYPE_FULL             = 'Full';
    const REFUND_TYPE_PARTIAL          = 'Partial';
    
    public function factory(Zend_Service_PayPal_Data_AuthInfo $authInfo, $options = array(), $apiMode = null)
    {
        if (! is_null($apiMode) && ($apiMode == self::API_MODE_NVP || $apiMode == self::API_MODE_SOAP)) {
            throw new Zend_Service_PayPal_Exception('$apiMode must be either "Nvp" or "Soap"');
        }
        
        if ($apiMode == self::API_MODE_SOAP) {
            throw new Zend_Service_PayPal_Exception('Soap is not yet supported');
        }
        
        return new Zend_Service_PayPal_Nvp($authInfo, $options);
    }
    
    /**
     * Validates the format of a transaction id
     * 
     * @return boolean
     */
    public static function validateTransactionId($transactionId)
    {
        require_once 'Zend/Validate.php';
        $validatorChain = new Zend_Validate();
        
        require_once 'Zend/Validate/StringLength.php';
        require_once 'Zend/Validate/Alnum.php';
        $validatorChain->addValidator(new Zend_Validate_StringLength(17))
                       ->addValidator(new Zend_Validate_Alnum());
        
        return $validatorChain->isValid($transactionId);
    }
    
    /**
     * Validate a 3-letter ISO currency code
     * 
     * @param  string $code
     * @return boolean
     */
    public static function validateCurrencyCode($code)
    {
        require_once 'Zend/Currency.php';
        $currency = new Zend_Currency();
        
        return preg_match('|^[A-Z]{3}$|', $code) &&
            in_array($code, array_keys($currency->getCurrencyList()));
    }
}
