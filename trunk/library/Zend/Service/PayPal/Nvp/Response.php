<?php 

/**
 * TODO: short description.
 * 
 * TODO: long description.
 * 
 */
class Zend_Service_PayPal_Nvp_Response extends Zend_Service_PayPal_Response
{

    /**
     * @var Zend_Http_Response
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_data;

    /**
     * TODO: short description.
     * 
     * @param Zend_Http_Response $response 
     * 
     */
    public function __construct( $response )
    {
        $this->_response = $response;
        parse_str( $response->getBody(), $this->_data );
    }
    
    /**
     * TODO: short description.
     * 
     * @return boolean
     */
    public function isSuccess()
    {

        if( !$this->_response->isSuccessful() ) {
            return false;
        }

        if( !isset( $this->_data['ACK'] ) )
        {
            return false;
        }
        
        if( substr( $this->_data['ACK'], 0 , 7 ) == 'Success' ) {
            return true;
        }
    }

    /**
     * TODO: short description.
     * 
     * @return boolean
     */
    public function isError()
    {
        if( !isset( $this->_data['ACK'] ) ) {
            return false; 
        }

        if( substr( $this->_data['ACK'], 0, 5 ) == 'Error' ) {
            return true;
        }
    }

    /**
     * TODO: short description.
     * 
     * @return boolean
     */
    public function isFailure()
    {
        if( !$this->_response->isSuccessful() ) {
            return true;
        }

        if( !isset( $this->_data['ACK'] ) ) {
            return true;
        }

        return false;
    }

    /**
     * TODO: short description.
     * 
     * @return string
     */
    public function getValue( $key )
    {
        $key = strtoupper($key);
        if( isset( $this->_data[$key] ) ) {
            return $this->_data[$key];
        }

        return null;
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function getTimestamp()
    {
        return $this->_data['TIMESTAMP'];
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function getCorrelationId()
    {
        return $this->_data['CORRELATIONID'];
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function getVersion()
    {
        return $this->_data['VERSION'];
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function getBuild()
    {
        return $this->_data['BUILD'];
    }

    /**
     * TODO: short description.
     * 
     * @return TODO
     */
    public function getAck()
    {
        return $this->_data['ACK'];
    }
}
