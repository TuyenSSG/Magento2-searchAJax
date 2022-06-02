<?php

/**
 * Observer to handle event
 * Sends JSON data to URL specified in extensions admin settings
 *
 * @author Chris Sohn (www.gomedia.co.za)
 * @copyright  Copyright (c) 2015 Go Media
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class GoMedia_Webhook_Model_Observer {

    /**
     * Used to ensure the event is not fired multiple times
     * http://magento.stackexchange.com/questions/7730/sales-order-save-commit-after-event-triggered-twice
     *
     * @var bool
     */
    private $_processFlag = false;

    /**
     * Posts order
     *
     * @param Varien_Event_Observer $observer
     * @return GoMedia_Webhook_Model_Observer
     */
    public function postOrder($observer) {
       
        // make sure this has not already run
        if (!$this->_processFlag) {
            /** @var $order Mage_Sales_Model_Order */
            $order = $observer->getEvent()->getOrder();
            $orderStatus = $order->getStatus();
            $url = Mage::getStoreConfig('webhook/order/url', $order['store_id']);
            if (!is_null($orderStatus) && $url) {
                $this->_processFlag = true;
                $order->save();
            }
            
        }

        if($this){
            return $this->GetOrder($order,$url,$observer);
        }
        return $this;
        
    }
    private function GetOrder($order,$url,$observer) {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getIncrementId();
        $CodeSeri = $order->getSerialCodes();
        $orderItems = $order->getAllItems();
        $incrementIda=$orderId;
        $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        $product = [];
        $productID="";
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $iduser = $customer->getId();
        //lấy thông tin id user
        $address = Mage::getModel('customer/address')->load($iduser);
        $country_id = $address->getCountryId();

        $getCountry = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCountryId();
        // if(!empty($getCountry)){
            $countryModel = Mage::getModel('directory/country')->loadByCode($getCountry);
            $countryName = $countryModel->getName();
        // }
        
        $resource = Mage::getSingleton('core/resource');

        //code old

        // $writeConnection = $resource->getConnection('core_write');
        // $readConnection = $resource->getConnection('core_read');
        // $table = $resource->getTableName('serialcodes');
        // $serial_number='';
        // $select = $writeConnection->select()->from(['tbl' => $table]);
        // $result = $writeConnection->query($select);
        // $results = $result->fetchAll();

        // // $order1 = $observer->getEvent()->getOrder();
        // // $source = 'pending';
        // // $a= $order1->getSerialCodeIds();
        // // var_dump($order);
        // // die();

        // foreach ($results as $itemsdb){
        //     if($itemsdb['orderinc'] == $incrementIda){
        //         $serial_number=$itemsdb['code'];
        //     }
        // }

        //code new KORG
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('serialcodes');
        $serial_number=array();
        $sku123=array();
        // die();
        $select = $writeConnection->select()
                                  ->from(['tbl' => $table])
                                  ->where('orderinc = ?', $incrementIda);
        $result = $writeConnection->query($select);
        $results = $result->fetchAll();

        foreach ($results as $itemsdb){
            if($itemsdb['orderinc'] == $incrementIda){
                // $serial_number[]=$itemsdb['code'];
                // $sku123[]=$itemsdb['sku'];
                 $serial_number[] = array(
                    "code" => $itemsdb['code'] ,
                    "sku" => $itemsdb['sku'] 
                );
                
                // $serial_number1=$itemsdb['code'];
            }

        }
         //code old
        $items = array();
        $itemcheck = array();
        $productIDs= array();

         //code old

        // foreach ($quote->getAllItems() as $item) {
        //     $checkedsp=Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('korg_id_auto_registration');
        //     $checkprice=Mage::getModel('catalog/product')->load($item->getProductId())->getPrice();
        //     if ( $checkedsp =="YES" || intval($checkprice) == 0){
        //         $_product= Mage::getSingleton('catalog/product')->load($item->getProductId());
                
        //          // $_product= Mage::getSingleton('catalog/product')->load($sku123[$key]);
        //         $productIDs[] = $_product->getResource()->getAttribute('korg_id_product_id')->getFrontend()->getValue($_product);
        //         // $productIDs1 = $_product->getResource()->getAttribute('korg_id_product_id')->getFrontend()->getValue($_product);
        //         $productIDss= implode(' and ', $productIDs);
        //         $serial_numberss= implode(' and ', $serial_number);
        //     }
             
        // }
        // test1
        // $count = 0;
        // foreach($serial_number as $value){

        //     $data = "product=$productIDs[count]&serial_number=$value&dealer_country=$countryName";
        //     $count++;
        //     $response = $this->proxy($data, $url);
        //    if (empty($serial_numberss) || !$serial_numberss){
        //        return $this;
        //     }
        //     else {
        //         $this->SendMa($response,$data);
        //     }
        // }

        foreach($serial_number as $index ){

            // $checkedsp=Mage::getModel('catalog/product')->load($index->getProductId())->getAttributeText('korg_id_auto_registration');
            // var_dump($checkedsp);
            // die();
            $productId = Mage::getModel('catalog/product')->getIdBySku($index['sku']);
            $checkedsp=Mage::getModel('catalog/product')->load($productId)->getAttributeText('korg_id_auto_registration');
            $_product= Mage::getSingleton('catalog/product')->load($productId);
            $productNames = $_product->getResource()->getAttribute('korg_id_product_id')->getFrontend()->getValue($_product);
            $data = "product=".$productNames."&serial_number=".$index['code']."&dealer_country=".$countryName;
            $response = $this->proxy($data, $url);
            if ( $checkedsp =="YES"){
                $this->SendMa($response,$data);
            }
            
        }
        
        // return $this;
        
         
        
        // if (empty($serial_numberss) || !$serial_numberss){
        //    return  $this;
        // }
        // else {
        //      return  $this->SendMa($response,$data);
        // }
       
        
    }
    private function SendMa($response,$data) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $iduser = $customer->getId();
        if($response->status !=200){
            $url_email_name= Mage::getStoreConfig('trans_email/ident_general/name');
            $url_email= Mage::getStoreConfig('trans_email/ident_general/email');
            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $t=time();
            $to_email = $url_email;
            $subject = "Error code";
            $body ="ID User:" .$iduser ."\n".
            "Time:".date('Y-m-d h:i:sa',$currentTimestamp)."\n".
            'Error Code :'.$response->status."\n".
            'Error:'.$response->body."\n".
            'Data Sent:'.$data."\n";
            // 'OrderID:'.$orderId;
            $headers = "From: Admin J-Grab";
            $headers = "Content-Type: text/html; charset=UTF-8";
            // mail($to_email, $subject, $body, $headers)->send();
            // $mail;
            $mail = Mage::getModel('core/email');
            // $mail->setToName($url_email_name); //send the name
            $mail->setToEmail($url_email);  //Set email
            $mail->setBody($body);
            $mail->setSubject('Error code'); //Set email subject
            $mail->setType('Content-Type: text/html; charset=UTF-8');
            $mail->send();
        }
    }

    /**
     * Curl data and return body
     *
     * @param $data
     * @param $url
     * @return stdClass $output
     */
    
    private function proxy($data, $url) {
        $apitest=$_SESSION['korgapitoken'] ;
        // $apitest='3d31147d9610f327202387ebdb26817f02a29f4b9913644a0eb945e01934683b';
        // var_dump($apitest);
        // die();
        $access_token = $apitest;
        // var_dump(json_encode($data));
        $ch = curl_init();
        // var_dump($body);
        $content = "grant_type=client_credentials";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION ,true);
        curl_setopt($ch,CURLOPT_ENCODING ,'');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60 * 2); // 2 minutes to connect
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); // 8 minutes to fetch the response
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // ignore cert issues
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer '.$access_token
        ));
        // curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        

        // execute
        $response = curl_exec($ch);
        // var_dump($response);
        // die();
        $output = new stdClass();
        $output->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // var_dump(curl_getinfo($ch, CURLOPT_HTTPHEADER));
        curl_close($ch);
       
        // die();
        // handle response
        $arr = explode("\r\n\r\n", $response, 2);
        if (count($arr) == 2) {
            $output->header = $arr[0];
            $output->body = $arr[1];
        } else {
            $output->body = "Send Mail Success";
        }
        return $output;
    }
    function getAccessToken() {
        $token_url = "https://id.korg.com/oauth/token?scope=profile";
        // $token_url .= "scope=$scope&";
        //	client (application) credentials on apim.byu.edu
        $client_id = "c6e1b3805db3f74a949a644dea55bd7faa11407ff9409c224f6ede6419c07338";
        $client_secret = "a8b0ce6409eeefaf1d2130df9ef35a5dc6511527b1b2d946c75b29529f9abdad";
        $content = "grant_type=client_credentials";
       
        $authorization = base64_encode("$client_id:$client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $content,
            
            // 'Grant-type:client_credentials',
            
        ));
        $response = curl_exec($curl);
        return json_decode($response)->access_token;
    }
    
    /**
     * Transform order into one data object for posting
     */
    /**
     * @param $orderIn Mage_Sales_Model_Order
     * @return mixed
     */
    private function transformOrder($orderIn) {
        $orderOut = $orderIn->getData();
        $orderOut['line_items'] = array();
        foreach ($orderIn->getAllItems() as $item) {
            $orderOut['line_items'][] = $item->getData();
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($orderIn->getCustomerId());
        $orderOut['customer'] = $customer->getData();
        $orderOut['customer']['customer_id'] = $orderIn->getCustomerId();

        /** @var $shipping_address Mage_Sales_Model_Order_Address*/
        $shipping_address = $orderIn->getShippingAddress();
        $orderOut['shipping_address'] = $shipping_address->getData();

        /** @var $shipping_address Mage_Sales_Model_Order_Address*/
        $billing_address = $orderIn->getBillingAddress();
        $orderOut['billing_address'] = $billing_address->getData();

        /** @var $shipping_address Mage_Sales_Model_Order_Payment*/
        $payment = $orderIn->getPayment()->getData();

        // remove cc fields
        foreach ($payment as $key => $value) {
            if (strpos($key, 'cc_') !== 0) {
                $orderOut['payment'][$key] = $value;
            }
        }

        /** @var $orderOut Mage_Core_Model_Session */
        $session = Mage::getModel('core/session');
        $orderOut['visitor'] = $session->getValidatorData();
        return $orderOut;
    }
}