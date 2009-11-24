<?php 
require_once 'Zend/Date.php';

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class Zend_Service_PayPal_Data_CreditCard
{

    const TYPE_VISA = 'Visa';
    const TYPE_MASTERCARD = 'MasterCard';
    const TYPE_DISCOVERY  = 'Discover';
    const TYPE_AMERICANEXPRESS = 'American Express';

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    public $type;

    /**
     * TODO: description.
     * 
     * @var array
     */
    public $accountNumber;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    public $expiration;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    public $cvv2;

    /**
     * TODO: description.
     * 
     * @var string
     */
    public $startDate;

    /**
     * TODO: description.
     * 
     * @var int
     */
    public $issueNumber;

    /**
     * TODO: short description.
     * 
     * @param mixed   
     * 
     */
    public function __construct( array $params = array() ) 
    {
        foreach ($params as $prop => $value) {
            $this->{$prop} = $value;
        }
    }

    /**
     * TODO: short description.
     * 
     * @param string $type 
     * 
     * @return void
     */
    public function setCardType( $type ) 
    {
        $this->type = $type;
    }

    /**
     * TODO: short description.
     * 
     * @return string
     */
    public function getCardType()
    {
        return $this->type;
    }

    /**
     * TODO: short description.
     * 
     * @param string $number 
     * 
     * @return void
     */
    public function setAcctNumber( $number )
    {
        $this->accountNumber = $number;
    }

    /**
     * TODO: short description.
     * 
     * @return string
     */
    public function getAcctNumber()
    {
        return $this->accountNumber;
    }

    /**
     * TODO: short description.
     * 
     * @param double $date 
     * 
     * @return void
     */
    public function setExpiration( $date )
    {
        $this->expiration = $date;
    }

    /**
     * TODO: short description.
     * 
     * @return string
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * TODO: short description.
     * 
     * @param mixed $cvv2 
     * 
     * @return void
     */
    public function setCvv2( $cvv2 )
    {
        $this->cvv2 = $cvv2;
    }

    /**
     * TODO: short description.
     * 
     * @return string
     */
    public function getCvv2()
    {
        return $this->cvv2;
    }

    /**
     * TODO: short description.
     * 
     * @param double $date 
     * 
     * @return void
     */
    public function setStartDate( $date )
    {
        $this->startDate = $date;
    }

    /**
     * TODO: short description.
     * 
     * @return double
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * TODO: short description.
     * 
     * @param double $date 
     * 
     * @return void
     */
    public function setIssueNumber( $issueNumber )
    {
        $this->issueNumber = $issueNumber;
    }

    /**
     * TODO: short description.
     * 
     * @return array
     */
    public function toNvp()
    {
        $data = array();
        $data['CREDITCARDTYPE'] = $this->type;
        $data['ACCT']           = $this->accountNumber;
        $data['EXPDATE']        = $this->expiration;
        $data['CVV2']           = $this->cvv2;
        $data['STARTDATE']      = $this->startDate;

        return array_filter( $data );
    }
    
    private function _validateAccountNumber($accountNumber)
    {
        require_once 'Zend/Validate/CreditCard.php';
        $ccValidator = new Zend_Validate_CreditCard(array('type', $this->getCardType()));
        if(!$ccValidator->isValid($acct)) {
            $messages = $ccValidator->getMessages();
            require_once 'Zend/Service/PayPal/Data/Exception.php';
            throw new Zend_Service_PayPal_Data_Exception($messages[0]);
        }
    }
}
