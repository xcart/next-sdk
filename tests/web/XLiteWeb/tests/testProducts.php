<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\tests;

/**
 * Description of testProducts
 *
 * @author givi
 */
class testProducts  extends \XLiteWeb\AXLiteWeb {
    
    /**
     * @dataProvider provider
     */
    public function testAddProduct($dataset) {
        $addProduct = $this->AdminProductAdd;
        $addProduct->load(true);
        $this->assertTrue($addProduct->validate(), 'Error validating add product page.');
        $categoryName = $addProduct->selectRandomCategory();
        $addProduct->fillForm($dataset['testData']);
        $addProduct->addProduct();
        
        $productId= $addProduct->getProductId();
        
        $storeFront = $this->CustomerIndex;
        $storeFront->load();
        $this->assertTrue($storeFront->validate(), 'Storefront is inaccessible.');
        
        $categoryLink = $storeFront->categoriesBox_getLink($categoryName);
        $categoryLink->click();
        
        
        $category = $this->CustomerCategory;
        $category->componentItemList->setItemPerPage('999');
        
        if ($dataset['results']['availInStorefront']) {
            $this->assertTrue($category->componentItemList->isProductExist($productId),'Product not accessible in store front.');
        } else {
            $this->assertFalse($category->componentItemList->isProductExist($productId),'Product is accessible in store front.');
        }
    }
    
    public function provider()
    {
        $datasets = array();
        $datasets['Usual product'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'product-name'     => 'Test product',
                'posteddata-brief-description'    => 'Test product <b>brief</b> description.',
                'posteddata-description'     => 'Descrip<b>t</b><i>i</i>on',
                'product-price'    => '123.45',
                'weight'   => '12',
                'enabled'  => 'Yes',
            ),
            'results'=>array(
                'availInStorefront'=>true,
            )
        ));
        
        $datasets['Disabled product'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'product-name'     => 'Test product 2',
                'posteddata-brief-description'    => 'Test product 2 <b>brief</b> description.',
                'posteddata-description'     => 'Descrip<b>t</b><i>i</i>on',
                'product-price'    => '0',
                'weight'   => '1',
                'enabled'  => 'No',
            ),
            'results'=>array(
                'availInStorefront'=>false,
            )
        ));

        return $datasets;
    }
    
}
