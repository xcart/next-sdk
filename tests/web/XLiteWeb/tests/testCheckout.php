<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\tests;

/**
 * Description of testCheckout
 *
 * @author givi
 */
class testCheckout extends \XLiteWeb\AXLiteWeb{
    /**
     * @dataProvider provider
     */
    public function testGuestCheckout($dataSet) {
        $testData = $dataSet['testData'];
        $results = $dataSet['results'];
        
        $storeFront = $this->CustomerIndex;
        $storeFront->load();
        $this->assertTrue($storeFront->validate(), 'Storefront is inaccessible.');
        
        $categoryLink = $storeFront->categoriesBox_getLink($testData['category']);
        $categoryLink->click();
        
        $category = $this->CustomerCategory;
        $category->componentItemList->setItemPerPage('999');
        $this->assertTrue($category->componentItemList->isProductExist($testData['productId']),'Product not accessible in store front.');
        $category->componentItemList->
                productName($testData['productId'])->click();
        $product = $this->CustomerProduct;
        $this->assertTrue($product->validate(), 'Opened page not the product page.');
        $product->addToCart();
        
        //чтобы нам не мешал попап просто переходим на хомпейдж.
        $storeFront->load();
        
        $countItems = $product->componentMiniCart->get_textItemCount->getText();
        $this->assertTrue($countItems == '1', 'Wrong item count in the cart.');
        $product->componentMiniCart->click();
        $product->componentMiniCart->get_buttonCheckout->click();
        
        $checkout = $this->CustomerCheckout;
        $this->assertTrue($checkout->validate(), 'This is not checkout page.');
        if ($testData['guest'] === true) {
            $checkout->get_buttonSignInAnonymous->click();
            $checkout->fillForm($testData['address']);
            $createAccount = $checkout->waitForCreateProfileCheckBox();
            if ($testData['createAccount'] === true) {
                $createAccount->click();
                $checkout->waitForPasswordField()->sendKeys($testData['password']);
                $checkout->get_buttonShowPassword->click();
                
            }
        } else {
            $checkout->get_inputLoginEmail->sendKeys($testData['address']['email']);
            $checkout->get_inputLoginPassword->sendKeys($testData['password']);
            $checkout->get_buttonSignIn->click();
        }
        //Кнопка мигаеет как новогодняя елка... 
        //пока не вижу нормального способа отследить 
        //окончание всех обновлений на чекауте
        sleep(2);
        $checkout->waitForPlaceOrderButton()->click();
        
        $invoice = $this->CustomerInvoice;
        $this->assertTrue($invoice->validate(), 'This is not invoice page.');
        
        $invoiceId = $invoice->getInvoiceNumber();
        
        $result = $invoice->checkAddresses($results['addressInInvoice']);
        $this->assertTrue($result, $result);
        $this->assertEquals($testData['address']['email'], $invoice->get_textEmail->getText(),'Еmail does not match.');
        
        $orders = $this->AdminOrders;
        $orders->load(true);
        $this->assertTrue($orders->validate(), 'This is not orders page.');
   
        $orders->get_inputSearchBy->sendKeys(str_pad($invoiceId, 5, '0', STR_PAD_LEFT));
        $orders->get_buttonSearch->click();
        $orderId = $orders->getOrderId();
        
        $status = $orders->selectStatus($orderId)->getFirstSelectedOption()->getText();
        $this->assertEquals('Queued', $status, 'Wrong orders staus after palce.');
        
        $orders->selectStatus($orderId)->selectByVisibleText('Processed');
        $orders->SaveChanges();
        
        $status = $orders->selectStatus($orderId)->getFirstSelectedOption()->getText();
        $this->assertEquals('Processed', $status, 'Wrong orders staus after processing.');
    }
    
        public function provider()
    {
        $email = substr(uniqid(), 0, 16) . '@cdev.ru';
        //for debug
        //$email = '532aecd3f3e0e@cdev.ru';
        $address = array(
            'shippingaddress-firstname' => 'User',
            'shippingaddress-lastname'  => 'Userovich',
            'shippingaddress-street'   => 'Address',
            'shippingaddress-city'      => 'Moody',
            'shippingaddress-country-code'   => 'US',
            'shippingaddress-state-id'     => '148',
            'shippingaddress-zipcode'   => '35004',
            'shippingaddress-phone'     => '88885555555',
            'email'     => $email,
        );
        $addressInInvoice = array(
            'firstname' => 'User',
            'lastname'  => 'Userovich',
            'street'   => 'Address',
            'city'      => 'Moody',
            'country_code'   => 'United States',
            'state_id'     => 'Alabama',
            'zipcode'   => '35004',
            'phone'     => '88885555555',
        );
        
        $datasets = array();
        $datasets['guest'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'guest' => true,
                'createAccount' => true,
                'category'     => 'Toys',
                'productId'    => '35',
                'address'     => $address,
                'password' => '123',
            ),
            'results'=>array(
                'CanPlaceOrder'=>true,
                'addressInInvoice'=>$addressInInvoice,
            )
        ));
        //for debug
        //$datasets = array();
        $datasets['Registred'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'guest' => false,
                'createAccount' => false,
                'category'     => 'Toys',
                'productId'    => '35',
                'address'     => $address,
                'password' => '123',
            ),
            'results'=>array(
                'CanPlaceOrder'=>true,
                'addressInInvoice'=>$addressInInvoice,
            )
        ));
        
        return $datasets;
    }
}
