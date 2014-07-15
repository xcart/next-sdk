<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteIntegration\Logic\Import\Processor;

class AProcessorTest extends \XLiteIntegration\Logic\Import\Processor\AProcessor
{
    public function testVerifyValueAsEmpty()
    {
        $importer = new \XLite\Logic\Import\Importer();
        $processor = new \XLite\Logic\Import\Processor\Products($importer);
        $method = new \ReflectionMethod($processor, 'verifyValueAsEmpty');
        $method->setAccessible(TRUE);
        $this->assertFalse($method->invoke(new $processor($importer), array('a')));
        $this->assertTrue($method->invoke(new $processor($importer), array('')));
    }

    /**
     * @dataProvider providerGetFiles
     */

    public function testGetFiles($model, $expected)
    {
        $list = array("/path/products_001.csv", "/path/categories_001.csv", "/path/customers_001.csv", "/path/aaa.csv");
        $model = "\\XLite\\Logic\\Import\\Processor\\" . $model;
        $files = array();
        foreach($list as $element) {
            $files[] = new \SplFileInfo($element);
        }

        $rclass = new \ReflectionClass($model);
        $method = $rclass->getMethod('getFiles');
        $method->setAccessible(TRUE);

        $stub = $this->getMockBuilder('\XLite\Logic\Import\Importer')
                    ->setMethods(array('getCSVList'))
                    ->getMock();
        $stub->expects($this->any())
            ->method('getCSVList')
            ->will($this->returnValue($files));

        $processor = new $model($stub);
        $result = $method->invoke($processor);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testGetFiles
     */
    public function providerGetFiles()
    {
        return array(
            array('Customers', array("/path/customers_001.csv")),
            array('Products', array("/path/products_001.csv")),
            array('Categories', array("/path/categories_001.csv")),
        );
    }

    /**
     * @dataProvider providerImportData
     */

    public function testImportData($model, $expected, $data)
    {
        $table = $this->getTable($model);
        $index = $this->getIndex($model);
        $model = '\\XLite\\Logic\\Import\\Processor\\' . $model;

        $importer = new \XLite\Logic\Import\Importer();
        $rclass = new \ReflectionClass($model);
        $method = $rclass->getMethod('importData');
        $method->setAccessible(TRUE);

        $processor = new $model($importer);
        $res = $method->invoke($processor, $data);

        $connection = \XLite\Core\Database::getEM()->getConnection();
        $query = sprintf('SELECT * from %s where %s="%s"', $table, $index, $data[$index]);
        $result = $connection->executeQuery($query)->fetchAll();

        #Cleanup
        $query = sprintf('DELETE from %s where %s="%s"', $table, $index, $data[$index]);
        if ($res) {
            $num = $connection->executeUpdate($query);
            $this->assertEquals(1, $num);
        }

        #Assertions
        $this->assertTrue($res);
        foreach($expected as $key=>$value) {
            $this->assertEquals($expected[$key], $result[0][$key]);
        }
        #Running importData() more than once without above DELETE results in multiple NULL categories. Why?
        $query = sprintf('SELECT * from %s where %s IS NULL', $table, $index);
        $this->assertLessThan(2, (count($connection->executeQuery($query)->fetchAll())));
    }

    public function providerImportData()
    {
        return array(
                array('Categories', array('cleanURL'=>"asdfg", 'parent_id'=>"1", 'useClasses'=>"A", 'enabled'=>"1"),
                        array('enabled'=>"Yes", 'path'=>"asdf", 'cleanURL'=>"asdfg")),
                array('Products', array('sku'=>"1001", 'cleanURL'=>"asdfg", 'product_class_id'=>"2", 'enabled'=>"1"),
                        array('enabled'=>"Yes", 'sku'=>"1001", 'cleanURL'=>"asdfg", 'productClass' => "Mobile phone", 'attributes'=>array())),
                # TBD: non-empty addressField
                array('Customers', array(),
                        array('login'=>"user@example.com", 
                            'addressField'=>array()) 
                            )
        );
    }


    /**
     * @dataProvider providerAssembleModelConditions
     */

    public function testAssembleModelConditions($model, $expected, $data)
    {
        $importer = new \XLite\Logic\Import\Importer();
        $model = "\\XLite\\Logic\\Import\\Processor\\" . $model;

        $rclass = new \ReflectionClass($model);
        $method = $rclass->getMethod('assembleModelConditions');
        $method->setAccessible(TRUE);
        $prop = $rclass->getProperty('columns');
        $prop->setAccessible(TRUE);

        $processor = new $model($importer);
        $prop->setValue($processor, null);

        $res = $method->invoke($processor, $data);
        $this->assertEquals($expected, $res);
    }

    public function providerAssembleModelConditions()
    {
        return array(
                array('Categories', array('path'=>'asdf'), array('path'=>"asdf", 'enabled'=>"Yes", 'showTitle'=>"Yes")),
                array('Categories', array('path'=>'Toys >>> cube'), array('path'=>"Toys >>> cube", 'enabled'=>"No")),
                array('Products', array('sku'=>'00002'), array('sku'=>"00002", 'memberships'=>array(), 'price'=>"19.99", 'cleanURL'=>"fghj")),
                array('Customers', array('login'=>'someuser'), array('login'=>"someuser", 'status'=>'E')),
                array('Categories', array('path'=>'igoods'), array('path'=>'igoods', 'cleanURL'=>'igoods'))
        );
    }

    public function getTable($model) 
    {
        $table = '';
        switch($model){
        case 'Categories':
            $table = 'xlite_categories';
            break;
        case 'Products':
            $table = 'xlite_products';
            break;
        case 'Customers':
            $table = 'xlite_profiles';
            break;
        }
        return $table;
    }

    public function getIndex($model)
    {
        $index = '';
        switch($model){
        case 'Categories':
            $index = 'cleanURL';
            break;
        case 'Products':
            $index = 'cleanURL';
            break;
        case 'Customers':
            $index = 'login';
            break;
        }
        return $index;
    }

}

