<?php

class Zend_Service_PayPal_Data_MassPayReceiver
{
    /**
     * Receiver type constants
     */
    const TYPE_EMAIL  = 'EmailAddress'; // Receiver identified by e-mail address 
  
    const TYPE_USERID = 'UserID'; // Receiver identified by PayPal user ID 
    
    /**
     * Receiver identifier(email address or PayPal ID)
     *
     * @var string
     */
    protected $_receiver;
    
    /**
     * Receiver ID type - one of the two type constants
     *
     * @var string
     */
    protected $_receiverType;
    
    /**
     * Amount to pay(currency is defined in transaction)
     *
     * @var float
     */
    protected $_amount;
    
    /**
     * Optional unique transaction ID
     *
     * @var string
     */
    protected $_uniqueid;
    
    /**
     * Optional customer-specific note
     *
     * @var string
     */
    protected $_note;
    
    /**
     * Create a new receiver info object
     *
     * @param float  $amount   		Amount to pay(> 0)
     * @param string $receiverId    Unique receiver
     * @param string $receiverType	receiver type, either EmailAddress or UserID
     */
    public function __construct( $amount = null, $receiverId = null, $receiverType = self::TYPE_EMAIL)
    {
        $this->setAmount( $amount );
        $this->setReceiver( $receiverId, $receiverType );
    }
    
    /**
     * Set the optional unique receiver ID
     *
     * @todo do we need to validate the unique id?
     * 
     * @param  string $id
     * @return Zend_Service_PayPal_Data_MassPayReceiver
     */
    public function setUniqueId( $id )
    {
        $this->_uniqueid = $id;
        return $this;
    }
    
    /**
     * Get the optional recipient unique ID
     *
     * @return string
     */
    public function getUniqueId()
    {
        return $this->_uniqueid;
    }
    
    /**
     * Set receiver specific custom note
     * 
     * @todo validation for note?
     *
     * @param  string $note
     * @return Zend_Service_PayPal_Data_MassPayReceiver
     */
    public function setNote( $note )
    {
        $this->_note = $note;
        return $this;
    }
    
    /**
     * Return the customer-specific optional custom note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->_note;
    }
    
    /**
     * Get the amount to pay(currency is determined by the transaction)
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }
    
    /**
     * Set the payment amount
     *
     * @throws Zend_Service_PayPal_Data_Exception
     * @param float $amount
     * @return Zend_Service_PayPal_Data_MassPayReceiver
     */
    public function setAmount( $amount )
    {
        if ( floatval( $amount ) <= 0 ) {
             require_once 'Zend/Service/PayPal/Data/Exception';
             throw new Zend_Service_PayPal_Data_Exception(
                 'Amount must be greater than 0' );
        }
        
        $this->_amount = number_format( floatval( $amount ), 2 );
        return $this;
    }
    
    /**
     * Sets the payment receiver
     * 
     * @todo should we really validate the email address here?
     *
     * @throws Zend_Service_PayPal_Data_Exception
     * @param string $receiverId	the paypal user to receive the payment
     * @param string $receiverType	the reciever type, either UserId or Email
     * @return Zend_Service_PayPal_Data_MassPayReceiver
     */
    public function setReceiver( $receiverId, $receiverType = self::TYPE_EMAIL )
    {
        $this->_receiverType = $receiverType;
        
        $validator = new Zend_Validate();
        $validator->addValidator( new Zend_Validate_StringLength( 0, 255 ) );
        
        if ( self::TYPE_EMAIL == $this->_receiverType ) {
            $validator->addValidator( new Zend_Validate_EmailAddress() );
        }
        //TODO add a validator for the UserID type here as well
        
        if ( !$validator->isValid( $receiverId ) ) {
                require_once 'Zend/Service/PayPal/Data/Exception';
                throw new Zend_Service_PayPal_Data_Exception(
                        'Invalid receiverId:' . join(', ', $validator->getMessages() ) );
        }
        
        $this->_receiver = $receiverId;
        
        return $this;
    }
    
    /**
     * Get the receiver ID(email address or PayPal ID)
     *
     * @return string
     */
    public function getReceiverId()
    {
        return $this->_receiver;
    }
    
    /**
     * Get the receiver type(must match transaction-global type)
     *
     * @return string
     */
    public function getReceiverType()
    {
        return $this->_receiverType;
    }
} 