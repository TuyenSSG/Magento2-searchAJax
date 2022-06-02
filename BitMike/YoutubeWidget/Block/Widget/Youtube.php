<?php 
namespace BitMike\YoutubeWidget\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface; 
 
class Youtube extends Template implements BlockInterface {

  protected $_template = "widget/youtube.phtml";

    protected $_catalogSession;
    protected $_customerSession;
    protected $_checkoutSession;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    )
    {        
        $this->_catalogSession = $catalogSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->session = $session;
        parent::__construct($context, $data);
    }
    public function setValue(){
        var_dump( $this->_customerSession->getCustomer()->getId()); //Print current customer ID

        $customerData = $this->_customerSession->getCustomer(); 
        var_dump($customerData->getData()); //Print current Customer Data
        $val =$this->_customerSession->getCustomer()->getFirstname();
        echo "1";
        // $val =$this->customerSession->getCustomer()->getFirstname();
        return $val;
     } 

    public function getValue(){
        $this->session->start();
        return $this->session->unsMessage();
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
        
    public function getCatalogSession() 
    {
        return $this->_catalogSession;
    }
    
    public function getCustomerSession() 
    {
        return $this->_customerSession;
    }
    
    public function getCheckoutSession() 
    {
        return $this->_checkoutSession;
    }    

}