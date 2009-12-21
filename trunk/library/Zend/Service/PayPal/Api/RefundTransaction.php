<?php

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class Zend_Service_PayPal_Api_RefundTransaction
    extends Zend_Service_PayPal_Api_AbstractApi
{
    public function execute()
    {
        $amount         = $this->getField( 'amt' );
        $transactionId  = $this->getField( 'transactionId' );
        $refundType     = $this->getField( 'refundType' );

        if (! Zend_Service_PayPal::validateTransactionId( $transactionId ) ) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('$transactionId is not a
            valid PayPal transaction id.  Value given: ' . $transactionId);
        }
        
        if (! in_array($refundType, array(
            Zend_Service_PayPal::REFUND_TYPE_OTHER, Zend_Service_PayPal::REFUND_TYPE_FULL, Zend_Service_PayPal::REFUND_TYPE_PARTIAL)))
        {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('Refund Type must be set to one of: "Other", "Full", or "Partial"');
        }
        
        if (! is_null($amount) && (! is_float($amount) || $amount <= 0)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('If passed, amount must be a floating-point number bigger than zero');
        }
        
        if ($refundType == Zend_Service_PayPal::REFUND_TYPE_PARTIAL && is_null($amount)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('Amount must be set if refund type is ' . Zend_Service_PayPal::REFUND_TYPE_PARTIAL);
        } elseif ($refundType == Zend_Service_PayPal::REFUND_TYPE_FULL && ! is_null($amount)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('Amount may not be set if refund type is ' . Zend_Service_PayPal::REFUND_TYPE_FULL);
        }
        
        
        return $this->sendRequest();
    }
}
