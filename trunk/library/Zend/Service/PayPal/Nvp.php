<?php


/** @see Zend_Http_Client */
require_once 'Zend/Http/Client.php';

/** @see Zend_Service_PayPal_Data_AuthInfo*/
require_once 'Zend/Service/PayPal/Data/AuthInfo.php';

require_once 'Zend/Service/PayPal/Nvp/Response.php';

class Zend_Service_PayPal_Nvp
{

    /**
     * Authentication info for the PayPal API service
     *
     * @var Zend_Service_PayPal_Data_AuthInfo
     */
    protected $_authInfo;
    
    /**
     * Zend_Http_Client to use for the service
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient;
    
    protected $_config = array(
        'endpoint' => 'https://api-3t.sandbox.paypal.com/nvp',
        'version'  => 59.0
    );

    /**
     * TODO: description.
     * 
     * @var string  Defaults to ''. 
     */
    protected $_lastResponse;
    
    /**
     * Enter description here...
     * 
     * @todo should we allow $authInfo to be passed in as an array?
     *
     * @param Zend_Service_PayPal_Data_AuthInfo $auth_info
     * @param Zend_Http_Client                  $httpClient
     */
    public function __construct(Zend_Service_PayPal_Data_AuthInfo $authInfo, array $options = array())
    {
        $this->_authInfo    = $authInfo;
        $this->_httpClient = new Zend_Http_Client();
        
        $this->_config = array_merge($this->_config, $options); 
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
     * Perform a DoDirectPayment call
     *
     * This call preforms a direct payment, directly charging a credit card
     * using PayPal's services. It does not require a valid PayPal user name,
     * but does require the credit card details and billing address of the
     * customer.
     *
     * @throws Zend_Service_PayPal_Exception
     * @param  Zend_Service_PayPal_Data_CreditCard $creditCard
     * @param  Zend_Service_PayPal_Data_Address    $address
     * @param  float                               $ammount
     * @param  string                              $remoteAddr
     * @param  string                              $paymentAction
     * @param  array                               $params
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function doDirectPayment($amount, Zend_Service_PayPal_Data_CreditCard $creditCard,
        Zend_Service_PayPal_Data_Address $billingAddress, Zend_Service_PayPal_Data_PayerName $payerName,
        $paymentAction = Zend_Service_PayPal::PAYMENT_ACTION_SALE, $ipAddress = null, array $params = array()
    )
    {

        if (is_null($ipAddress)) {
            if (! empty($_SERVER['REMOTE_ADDR'])) {
                require_once 'Zend/Service/PayPal/Exception.php';
                throw new Zend_Service_PayPal_Exception(
                    'client IP address is required by PayPal API.'
                );
            } else {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }
        }

        $params += $creditCard->toNvp();
        $params += $billingAddress->toNvp();
        $params += $payerName->toNvp();
        
        $params['IPADDRESS']     = $ipAddress;
        $params['AMT']           = $amount;
        $params['PAYMENTACTION'] = $paymentAction;
        
        return $this->_doMethodCall('DoDirectPayment', $params);
    }
    
    /**
     * Perform a SetExpressCheckout PayPal API call, starting an Express
     * Checkout process.
     *
     * This call is expected to return a token which can be used to redirect
     * the user to PayPal's transaction approval page
     *
     * @param  float  $amount
     * @param  string $returnUrl
     * @param  string $cancelUrl
     * @param  array  $params    Additional parameters
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function setExpressCheckout($amount, $returnUrl, $cancelUrl, array $params = array())
    {
        //----------------------------------
        // TODO Verify return url and cancel url
        //----------------------------------
        $params['AMT']       = $amount;
        $params['RETURNURL'] = $returnUrl;
        $params['CANCELURL'] = $cancelUrl;

        return $this->_doMethodCall('SetExpressCheckout', $params);
    }
    
    /**
     * Perform a GetExpressCheckoutDetails PayPal API call, requesting info
     * about a started Express Checkout transaction
     *
     * @param  string $token Transaction identifier token
     * @return Zend_Service_PayPal_Nvp_Response
     */
    
    public function getExpressCheckoutDetails($token)
    {
        $data = array(
            'TOKEN' => $token
        );

        return $this->_doMethodCall('GetExpressCheckoutDetails', $data);
    }
    
    /**
     * Perform a DoExpressCheckoutPayment PayPal API call, finalizing a
     * transaction.
     *
     * @param  string $token
     * @param  string $payerId
     * @param  string $amount
     * @param  string $paymentAction Payment action - 'Sale' or 'Authorization'
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function doExpressCheckoutPayment($token, $payerId, $amount, $paymentAction = Zend_Service_PayPal::PAYMENT_ACTION_SALE)
    {

        $data = array(
            'TOKEN'         => $token,
            'PAYERID'       => $payerId,
            'AMT'           => $amount,
            'PAYMENTACTION' => $paymentAction,
        );

        return $this->_doMethodCall('DoExpressCheckoutPayment', $data);
    }
    
    /**
     * Perform a DoVoid PayPal API call, canceling an authorization.
     *
     * @param string $authorizationId
     * @param string $note
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function doVoid($authorizationId, $note = null)
    {
        if (! Zend_Service_PayPal::validateTransactionId($authorizationId)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('$authorizationId is not a valid PayPal transaction id.  Value given: ' . $authorizationId);
        }
        
        $params = array(
            'AUTHORIZATIONID' => $authorizationId,
        );
        
        if(!is_null($note)) {
            $params['NOTE'] = $note;
        }
        
        return $this->_doMethodCall('DoVoid', $params);
    }
    
    /**
     * Perform a DoReferenceTransaction PayPal API call.
     *
     * @param string $referenceId
     * @param string $paymentAction
     * @param int    $returnFmfDetails
     * @param string $softDescriptor
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function doReferenceTransaction($referenceId, $amount, $paymentAction = Zend_Service_PayPal::PAYMENT_ACTION_SALE,
        $returnFmfDetails = false, $softDescriptor = null, $custom = null, Zend_Service_PayPal_Data_Address $shipToAddress = null,
        Zend_Service_PayPal_Data_Address $billingAddress = null, Zend_Service_PayPal_Data_CreditCard $creditCard = null)
    {
        if (! Zend_Service_PayPal::validateTransactionId($referenceId)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('$referenceId is not a valid PayPal transaction id.  Value given: ' . $referenceId);
        }
        
        $paymentAction = ucfirst(strtolower((string) $paymentAction));
        if (! ($paymentAction == Zend_Service_PayPal::PAYMENT_ACTION_SALE || $paymentAction == Zend_Service_PayPal::PAYMENT_ACTION_AUTHORIZATION)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('Payment Action must be set to either "Sale" or "Authorization"');
        }
            
        $amount = (float) $amount;
        if (! $amount) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('Amount must be a floating-point number bigger than zero');
        }
        
        // convert to int for the api call
        $returnFmfDetails = (int)$returnFmfDetails;
        
        $params = array(
            'REFERENCEID'      => $referenceId,
            'PAYMENTACTION'    => $paymentAction,
            'RETURNFMFDETAILS' => $returnFmfDetails,
            'AMT'              => $amount,
            'CUSTOM'           => $custom,
            'SOFTDESCRIPTOR'   => $softDescriptor,
        );
        
        if ($billingAddress instanceof Zend_Service_PayPal_Data_Address) {
            $params = array_merge($params, $billingAddress->toNvp());
        }
        
        if ($creditCard instanceof Zend_Service_PayPal_Data_CreditCard) {
            $params = array_merge($params, $creditCard->toNvp());
        }

        return $this->_doMethodCall('DoReferenceTransaction', $params);
    }
    
    public function refundTransaction($transactionId, $refundType, $amount = null, $note = null)
    {
        if (! Zend_Service_PayPal::validateTransactionId($transactionId)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception('$transactionId is not a valid PayPal transaction id.  Value given: ' . $transactionId);
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
        
        $params = array(
            'TRANSACTIONID' => $transactionId,
            'REFUNDTYPE'    => $refundType,
        );
        
        if (! is_null($amount)) {
            $params['AMT'] = $amount;
        }
        
        if (! is_null($note)) {
            $params['NOTE'] = $note;
        }
        
        return $this->_doMethodCall('RefundTransaction', $params);
    }
    
    /**
     * Perform a capture on an existing authorization
     * 
     * @param string $authorizationId Transaction ID from previous authorization
     * @param float  $amount          Amount to capture (subject to capture limits)
     * @param string $completeType    'Complete' or 'NotComplete'
     * @param string $currencyCode    3 letter currency code, default is USD
     * @param string $invNum          optional
     * @param string $note            optional
     * @param string $softDescriptor  optional
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function doCapture($authorizationId, $amount, $completeType = 'Complete', $currencyCode = 'USD', 
        $invNum = null, $note = null, $softDescriptor = null)
    {
        
        $params = array(
            'AUTHORIZATIONID' => $authorizationId,
            'AMT'             => $amount,
            'CURRENCYCODE'    => $currencyCode,
            'COMPLETETYPE'    => $completeType,
        );
        
        return $this->_doMethodCall('DoCapture', $params);
    }
    
    /**
     * Perform a Mass Payment API call
     *
     * @param  array  $receivers    Array of Zend_Service_PayPal_Data_MassPayReceiver objects
     * @param  string $rcpttype     Receiver type
     * @param  string $emailSubject Email subject for all receivers
     * @param  string $currency     3 letter currency code, default is USD
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function massPay(array $receivers, $rcpttype = Zend_Service_PayPal_Data_MassPayReceiver::RT_EMAIL, $emailSubject = '', $currency = 'USD')
    {
        // Make sure we have more than 0 and less than 256 receivers
        if (empty($receivers) || count($receivers) > 255) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception("Number of receivers must be between 1 and 255");
        }
            
        // Validate receiver type
        if ($rcpttype == Zend_Service_PayPal_Data_MassPayReceiver::RT_EMAIL) {
            $idfield = 'L_EMAIL';
        } elseif ($rcpttype == Zend_Service_PayPal_Data_MassPayReceiver::RT_USERID) {
            $idfield = 'L_RECEIVERID';
        } else {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception("Receiver ID type '$rcpttype' is not valid");
        }

        // Validate currency code
        if (! Zend_Service_PayPal::validateCurrencyCode($currency)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception("Currency code '$currency' is not valid");
        }
        
        // Set currency code and receiver type
        $params = array(
            'RECEIVERTYPE' => $rcpttype,
            'CURRENCYCODE' => $currency
        );

        // Validate and optionally set email subject
        if ($emailSubject) {
            if (strlen($emailSubject) > 255) {
                require_once 'Zend/Service/PayPal/Exception.php';
                throw new Zend_Service_PayPal_Exception("Email Subject must be up to 255 single-byte characters");
            }

            $params['EMAILSUBJECT'] = $emailSubject;
        }
        
        $c = 0;
        foreach ($receivers as $rcpt) { /* @var $rcpt Zend_Service_PayPal_Data_MassPayReceiver */
            if (! $rcpt->getReceiverType() == $rcpttype) {
                require_once 'Zend/Service/PayPal/Exception.php';
                throw new Zend_Service_PayPal_Exception("All receiver ID types must be '$rcpttype', '{$rcpt->getReceiverId()}' is not"); 
            }

            // Set amount and receiver ID
            $params["L_AMT$c"]     = $rcpt->getAmount();
            $params[$idfield . $c] = $rcpt->getReceiverId();
            
            // Set optional fields
            if ($rcpt->getUniqueId()) 
                $params["L_UNIQUEID$c"] = $rcpt->getUniqueId();
                
            if ($rcpt->getCustomNote())
                $params["L_NOTE$c"] = $rcpt->getCustomNote();
            
            $c++;
        }
        
        return $this->_doMethodCall('MassPay', $params);
    }
    
    /**
     * Get details about a transaction
     * 
     * @param  string $transactionId Transaction ID (17 Alphanumeric single-byte characters)
     * @return Zend_Service_PayPal_Nvp_Response
     */
    public function getTransactionDetails($transactionId)
    {
        if (! Zend_Service_PayPal::validateTransactionId($transactionId)) {
            require_once 'Zend/Service/PayPal/Exception.php';
            throw new Zend_Service_PayPal_Exception("'$transactionId' is not a valid PayPal transaction ID");
        }

        $params = array(
            'TRANSACTIONID' => $transactionId
        );
        
        return $this->_doMethodCall('GetTransactionDetails', $params);
    }
    
    /**
     * Sets the version of the NVP API to use
     *
     * @param float $version
     */
    public function setVersion($version)
    {
        $this->_config['version'] = $version;
    }

    /**
     * Converts keys to uppercase and urlencodes the values
     * 
     * @return array
     */
    protected static function _parseParams(array $aParams)
    {
        $data = array();
        foreach($aParams as $name => $value) {
            $data[strtoupper($name)] = urlencode($value);
        }

        return $data;
    }

    /**
     * TODO: short description.
     * 
     * @param array $aParams 
     * @deprecated 
     * 
     * @return Zend_Service_PayPal_Nvp_Response
     */
    protected function _doMethodCall($methodName, array $aParams)
    {
        //--------------------------------------
        // Add auth details to params
        //--------------------------------------
        $aParams['USER']      = $this->_authInfo->getUsername();
        $aParams['PWD']       = $this->_authInfo->getPassword();
        $aParams['SIGNATURE'] = $this->_authInfo->getSignature();
        $aParams['VERSION']   = $this->_config['version'];
        $aParams['METHOD']    = $methodName;
        
        // filter any null params
        $aParams = self::_parseParams(array_filter($aParams));

        $this->_httpClient->setParameterPost($aParams);

        $this->_httpClient->setUri($this->_config['endpoint']);
        $this->_lastResponse = 
            $this->_httpClient->request(Zend_Http_Client::POST);
        
        if(! $this->_lastResponse->isSuccessful()) {
            throw new Zend_Service_PayPal_Exception(
                'HTTP client response was not ok: ' . $this->_lastResponse->getStatus());
        }

        return new Zend_Service_PayPal_Nvp_Response($this->_lastResponse);
    }
} 
