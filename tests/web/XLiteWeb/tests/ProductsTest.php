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
        $addProduct->fillForm($dataset['testData']);
        $addProduct->addProduct();
        
        $productId= $addProduct->getProductId();
        $categoryName = $addProduct->getCategory($dataset['testData']['categories']);
        
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
        // Categories are hardcoded by 'value'
        $datasets = array();
        $datasets['Usual product'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'name'     => 'Test product',
                'brief-description'    => 'Test product <b>brief</b> description.',
                'description'     => 'Descrip<b>t</b><i>i</i>on',
                'price'    => '123.45',
                'weight'   => '12',
                'enabled'  => 'Y',
                'categories'=> '2'
            ),
            'results'=>array(
                'availInStorefront'=>true,
            )
        ));

        $datasets['Disabled product'] = array(
            array(
            'config'=>array(),
            'testData'=>array(
                'name'     => 'Test product 2',
                'brief-description'    => 'Test product 2 <b>brief</b> description.',
                'description'     => 'Descrip<b>t</b><i>i</i>on',
                'price'    => '0',
                'weight'   => '1',
                'enabled'  => 'N',
                'categories'=> '2'
            ),
            'results'=>array(
                'availInStorefront'=>false,
            )
        ));

        return $datasets;
    }
    
}
