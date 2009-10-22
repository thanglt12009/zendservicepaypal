<?php
class Zend_Service_PayPal_Data_Address
{
    /**
     * Street
     *
     * @var string
     */
    public $street;

    /**
     * TODO: description.
     * 
     * @var string
     */
    public $street2;
    
    /**
     * City
     *
     * @var string
     */
    public $city;
    
    /**
     * State (2 character code)
     *
     * @var string
     */
    public $state = 'ZZ';
    
    /**
     * Country (2 character code)
     *
     * @var string
     */
    public $countryCode;
    
    /**
     * Zip code
     *
     * @var integer
     */
    public $zip;

    /**
     * TODO: description.
     * 
     * @var mixed
     */
    public $phoneNum;
       /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function toNvp()
    {
        $data = array();
        $data['STREET']     = $this->street;
        $data['STREET2']    = $this->street2;
        $data['STATE']      = $this->state;
        $data['COUNTRYCODE']= $this->countryCode;
        $data['ZIP']        = $this->zip;
        $data['PHONENUM']   = $this->phoneNum;

        return array_filter( $data );
    }
} 
