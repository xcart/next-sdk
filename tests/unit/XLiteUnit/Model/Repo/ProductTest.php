<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Model\Repo;

/**
 * @coversDefaultClass \XLite\Model\Repo\Product
 */
class ProductTest extends \XLiteUnit\Model\Repo\ARepo
{
    // {{{ ARepo tests

    /**
     * Test getRepoType() method
     */
	public function testGetRepoType()
    {
        $repo = $this->getRepo();

        $result = $repo->getRepoType();

        $this->assertInternalType('string', $result, 'Result is not string');

        $this->assertEquals('store', $result, 'Unexpected repo type');
	}

    /**
     * Test getCacheDriver() method
     */
	public function testGetCacheDriver()
    {
        $repo = $this->getRepo();

        $result = $repo::getCacheDriver();

        $this->assertInstanceOf('\XLite\Core\Cache\Registry', $result, 'Result is not \XLite\Core\Cache\Registry object');
	}

    /**
     * Test getPublicClassMetadata() method
     */
	public function testGetPublicClassMetadata()
    {
        $repo = $this->getRepo();

        $result = $repo->getPublicClassMetadata();

        $this->assertInstanceOf('\Doctrine\ORM\Mapping\ClassMetadata', $result, 'Result is not \Doctrine\ORM\Mapping\ClassMetadata object');
	}

    /**
     * Test getDefaultAlias() method
     */
	public function testGetDefaultAlias()
    {
        $repo = $this->getRepo();

        $result = $repo->getDefaultAlias();

        $this->assertInternalType('string', $result, 'Result is not string');
        $this->assertEquals('p', $result, 'Unexpected alias');
	}

    /**
     * Test count() method
     */
	public function testCount()
    {
        $repo = $this->getRepo();

        $result = $repo->count();

        $this->assertInternalType('int', $result, 'Result is not integer');
	}

    /**
     * Test countBy() method
     */
	public function testCountBy()
    {
        $repo = $this->getRepo();

        $result = $repo->countBy(array('enabled' => 1));

        $this->assertInternalType('int', $result, 'Result is not integer');
	}

    /**
     * Test find() method
     */
	public function testFind()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();

        $result = $repo->find($product->getProductId());

        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');
        $this->assertEquals($product->getProductId(), $result->getProductId(), 'Unexpected product found (find by id)');

        $result = $repo->find(9999999999);
        $this->assertNull($result, 'Result is not null');

        $result = $repo->find(array('product_id' => $product->getProductId()));
        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');
        $this->assertEquals($product->getProductId(), $result->getProductId(), 'Unexpected product found (find by array)');
	}

    /**
     * Test findByIds() method
     */
	public function testFindByIds()
    {
        $repo = $this->getRepo();

        $products = $repo->findAll();
        $ids = array();
        $i = 0;
        foreach ($products as $p) {
            $ids[] = $p->getProductId();
            $i++;
            if ($i > 10) {
                break;
            }
        }

        $result = $repo->findByIds($ids);

        $this->assertInternalType('array', $result, 'Result is not array');

        $_ids = array();
        foreach ($result as $r) {
            $_ids[] = $r->getProductId();
        }
        $this->assertEquals($ids, $_ids, 'Unexpected products found');

        $result = $repo->findByIds(array(99999999));
        $this->assertInternalType('array', $result, 'Result is not array');
        $this->assertEmpty($result, 'Result is not empty');
	}

    /**
     * Test findFrame() method
     *
     * @dataProvider providerFindFrame
     */
	public function testFindFrame($start, $limit, $expected)
    {
        $repo = $this->getRepo();

        $result = $repo->findFrame($start, $limit);
        $this->assertInternalType('array', $result, 'Result is not array');
        $this->assertEquals($expected, count($result));
	}

    /**
     * Data provider for testFindFrame
     */
    public function providerFindFrame()
    {
        $repo = $this->getRepo();

        $products = $repo->findAll();
        $max = count($products);
        $half = intval($max / 2);
        $rest = $max - $half;

        return array(
            array(0, 0, $max),
            array(0, $max, $max),
            // array($half, 0, $rest), // This combination ($start > 0 and $limit = 0) causes PDO error!
            array($half, 1, 1),
        );
    }

    /**
     * Test findDetached() method
     */
	public function testFindDetached()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();

        $result = $repo->findDetached($product->getProductId());

        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');
        $this->assertEquals($product->getProductId(), $result->getProductId(), 'Unexpected product found (find by id)');

        $this->assertTrue($product->isDetached(), 'Result is not detached');

        $result = $repo->find(9999999999);
        $this->assertNull($result, 'Result is not null');
	}

    /**
     * Test generateCleanURL() method
     *
     * @dataProvider providerGenerateCleanURL
     */
	public function testGenerateCleanURL($name, $expected)
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();
        $oldName = $product->getName();
        $product->setName($name);
        $result = $repo->generateCleanURL($product);

        $product->setName($oldName);

        $this->assertInternalType('string', $result, 'Result is not string');
        $this->assertEquals($expected, $result);
	}

    /**
     * Data provider for testGenerateCleanURL
     */
    public function providerGenerateCleanURL()
    {
        return array(
            array('Planet Express Babydoll', 'planet_express_babydoll'),
            array('   345 \'dsd<script>Purga', '___345_-dsd-script-purga'),
        );
    }

    /**
     * Test countForExport() method
     */
	public function testCountForExport()
    {
        $repo = $this->getRepo();

        $result = $repo->countForExport();

        $this->assertInternalType('int', $result, 'Result is not integer');
	}

    /**
     * Test getExportIterator() method
     */
	public function testGetExportIterator()
    {
        $repo = $this->getRepo();

        $result = $repo->getExportIterator();

        $this->assertInstanceOf(
            '\Doctrine\ORM\Internal\Hydration\IterableResult',
            $result,
            'Result is not \Doctrine\ORM\Internal\Hydration\IterableResult object'
        );
	}

    /**
     * Test findOneByImportConditions() method
     */
	public function testFindOneByImportConditions()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();
        $conditions = array(
            'product_id' => $product->getProductId(),
        );

        $result = $repo->findOneByImportConditions($conditions);

        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');
        $this->assertEquals($product->getProductId(), $result->getProductId(), 'Unexpected result');
	}

    /**
     * Test insert(), update() and delete() methods
     */
	public function testInsertUpdateDelete()
    {
        $repo = $this->getRepo();

        $product = new \XLite\Model\Product();
        $product->setSku('test#1');
        $product->setCleanURL('test-1');

        $result = $repo->insert($product);

        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');

        $productId = $result->getProductId();

        $this->assertNotNull($productId, 'Product ID of inserted product is null');

        $repo->update($result, array('sku' => 'test#2'));

        $p = $repo->find($productId);

        $this->assertInstanceOf('\XLite\Model\Product', $p, 'Result is not \XLite\Model\Product object');
        $this->assertEquals('test#2', $p->getSku(), 'Product sku is not updated');

        $repo->delete($p);

        $p = $repo->find($productId);

        $this->assertNull($p, 'Product was not deleted');
	}

    /**
     * Test insert(), updateById() and deleteById() methods
     */
	public function testInsertUpdateDeleteById()
    {
        $repo = $this->getRepo();

        $product = new \XLite\Model\Product();
        $product->setSku('test#11');
        $product->setCleanURL('test-11');

        $result = $repo->insert($product);

        $this->assertInstanceOf('\XLite\Model\Product', $result, 'Result is not \XLite\Model\Product object');

        $productId = $result->getProductId();

        $this->assertNotNull($productId, 'Product ID of inserted product is null');

        $repo->updateById($productId, array('sku' => 'test#12'));

        $p = $repo->find($productId);

        $this->assertInstanceOf('\XLite\Model\Product', $p, 'Result is not \XLite\Model\Product object');
        $this->assertEquals('test#12', $p->getSku(), 'Product sku is not updated');

        $repo->deleteById($productId);

        $p = $repo->find($productId);

        $this->assertNull($p, 'Product was not deleted');
	}

    /**
     * Test insertInBatch(), updateInBatch() and deleteInBatch() methods
     */
	public function testInsertUpdateDeleteInBatch()
    {
        $repo = $this->getRepo();

        $product1 = new \XLite\Model\Product();
        $product1->setSku('test#21');
        $product1->setCleanURL('test-21');

        $product2 = new \XLite\Model\Product();
        $product2->setSku('test#22');
        $product2->setCleanURL('test-22');

        $result = $repo->insertInBatch(array($product1, $product2));

        $this->assertInternalType('array', $result, 'Result is not array');

        $ids = array();

        foreach ($result as $k => $r) {
            $this->assertInstanceOf('\XLite\Model\Product', $r, 'Result is not \XLite\Model\Product object');
            $this->assertNotNull($r->getProductId(), 'Product ID of inserted product is null');

            $ids[] = $r->getProductId();
        }

        $product1->setSku('test#31');
        $product2->setSku('test#32');

        $repo->updateInBatch(array($product1, $product2));

        $products = $repo->findByIds($ids);

        foreach ($products as $p) {
            $this->assertInstanceOf('\XLite\Model\Product', $p, 'Result is not \XLite\Model\Product object');
            $this->assertContains($p->getSku(), array('test#31', 'test#32'), 'Products sku is not updated');
        }

        $repo->deleteInBatch(array($product1, $product2));

        $products = $repo->findByIds($ids);

        $this->assertEmpty($products, 'Products was not deleted');
	}

    /**
     * Test insertInBatch(), updateInBatchById() and deleteInBatchById() methods
     */
	public function testInsertUpdateDeleteInBatchById()
    {
        $repo = $this->getRepo();

        $product1 = new \XLite\Model\Product();
        $product1->setSku('test#21');
        $product1->setCleanURL('test-21');

        $product2 = new \XLite\Model\Product();
        $product2->setSku('test#22');
        $product2->setCleanURL('test-22');

        $result = $repo->insertInBatch(array($product1, $product2));

        $this->assertInternalType('array', $result, 'Result is not array');

        $ids = array();

        foreach ($result as $k => $r) {
            $this->assertInstanceOf('\XLite\Model\Product', $r, 'Result is not \XLite\Model\Product object');
            $this->assertNotNull($r->getProductId(), 'Product ID of inserted product is null');

            $ids[] = $r->getProductId();
        }

        $data = array(
            $ids[0] => array('sku' => 'test#31'),
            $ids[1] => array('sku' => 'test#32'),
        );

        $repo->updateInBatchById($data);

        $products = $repo->findByIds($ids);

        foreach ($products as $p) {
            $this->assertInstanceOf('\XLite\Model\Product', $p, 'Result is not \XLite\Model\Product object');
            $this->assertContains($p->getSku(), array('test#31', 'test#32'), 'Products sku is not updated');
        }

        $repo->deleteInBatchById($data);

        $products = $repo->findByIds($ids);

        $this->assertEmpty($products, 'Products was not deleted');
	}


    // }}}

    // {{{ Product repository tests

    /**
     * Test findAllCleanURLs() method
     */
	public function testFindAllCleanURLs()
    {
        $repo = $this->getRepo();

        $result = $repo->findAllCleanURLs();

        $this->assertInternalType('array', $result, 'Result is not array');
        $this->assertNotEmpty($result);

        foreach ($result as $key => $value) {
            $this->assertInternalType('string', $key, 'Clean URL is not string');
            $this->assertInternalType('int', $value, 'Product ID is not integer');
            $this->assertNotEmpty($key, 'Clean URL is empty');
        }
	}

    /**
     * Test search() method
     */
	public function testSearch()
    {
        $repo = $this->getRepo();

        $result = $repo->search($this->getSearchConditions(), true);

        $this->assertInternalType('int', $result, 'Search result count is not integer');

        $result = $repo->search($this->getSearchConditions(), false);

        $this->assertInternalType('array', $result, 'Search result is not array');
    }

    /**
     * Test createQueryBuilder() method
     */
    public function testCreateQueryBuilder()
    {
        $repo = $this->getRepo();

        $result = $repo->createQueryBuilder();

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $result, 'Result is not \Doctrine\ORM\QueryBuilder object');
    }

    /**
     * Test searchCount() method
     */
	public function testSearchCount()
    {
        $repo = $this->getRepo();

        $result = $repo->searchCount($this->getQueryBuilder());

        $this->assertInternalType('int', $result, 'Search result count is not integer');
    }

    /**
     * Test searchResult() method
     */
	public function testSearchResult()
    {
        $repo = $this->getRepo();

        $result = $repo->searchResult($this->getQueryBuilder());

        $this->assertInternalType('array', $result, 'Search result is not array');
    }

    /**
     * Test findOneByCleanURL() method
     */
    public function testFindOneByCleanURL()
    {
        $repo = $this->getRepo();

        $cleanURLs = $repo->findAllCleanURLs();
        $this->assertNotEmpty($cleanURLs);

        foreach ($cleanURLs as $url => $productId) {
            $this->assertNotEmpty($url, 'Clean URL is empty');
            $product = $repo->findOneByCleanURL($url);
            $this->assertInstanceOf('\XLite\Model\Product', $product, sprintf('Result is not \XLite\Model\Product instance (clean URL = "%s")', $url));
            $this->assertEquals($productId, $product->getProductId(), sprintf('Result product ID (%s) differs from expected product ID (%s)', $product->getProductId(), $productId));
            break; // Make one iteration only
        }

    }

    /**
     * Test countLastUpdated() method
     */
	public function testCountLastUpdated()
    {
        $repo = $this->getRepo();

        $limit = 0;
        $result = $repo->countLastUpdated($limit);

        $this->assertInternalType('int', $result, 'Result is not integer');
        $this->assertGreaterThanOrEqual(0, $result, 'Result is less than zero');
    }

    /**
     * Test getRESTNames() method
     */
	public function testGetRESTNames()
    {
        $repo = $this->getRepo();

        $result = $repo->getRESTNames();

        $this->assertInternalType('array', $result, 'Result is not array');

        $expected = array('product');

        $this->assertEquals($expected, $result, 'Result is differ from expected');
    }

    /**
     * Test getProductREST() method
     */
	public function testGetProductREST()
    {
        $repo = $this->getRepo();

        $result = $repo->getProductREST(999999999);
        $this->assertNull($result, 'Check non-existing product: Result is not null');

        $product = $this->getProduct();
        $result = $repo->getProductREST($product->getProductId());

        $this->assertInternalType('array', $result, 'Result is not array');
        $this->assertNotEmpty($result, 'Result is empty');

        foreach ($result as $field => $value) {
            $method = 'get' . \XLite\Core\Converter::convertToCamelCase($field);
            $this->assertEquals($product->$method(), $value, 'Check field ' . $field);
        }
    }

    /**
     * Test getImportIterator() method
     */
	public function testGetImportIterator()
    {
        $repo = $this->getRepo();

        $result = $repo->getImportIterator();

        $this->assertInstanceOf(
            '\Doctrine\ORM\Internal\Hydration\IterableResult',
            $result,
            'Result is not \Doctrine\ORM\Internal\Hydration\IterableResult object'
        );
    }

    /**
     * Test assignExternalEnabledCondition() method
     */
	public function testAssignExternalEnabledCondition()
    {
        $repo = $this->getRepo();

        $qb = $this->getQueryBuilder();

        $result = $repo->assignExternalEnabledCondition($qb, 'q');

        $this->assertInstanceOf(
            '\XLite\Model\QueryBuilder\AQueryBuilder',
            $result,
            'Result is not \XLite\Model\QueryBuilder\AQueryBuilder object'
        );

        $this->assertRegExp('/q.enabled = :enabled/', $qb->getDql(), 'Enabled condition is not found in DQL');
    }

    /**
     * Test generateSKU() method
     */
	public function testGenerateSKU()
    {
        $repo = $this->getRepo();

        $product = new \XLite\Model\Product();
        $product->setProductId(12345);

        $result = $repo->generateSKU($product);

        $this->assertInternalType('string', $result, 'Result is not string');
        $this->assertEquals('00000012345', $result, 'Unexpected SKU result');

        $product->setProductId(123456789012);

        $result = $repo->generateSKU($product);

        $this->assertInternalType('string', $result, 'Result is not string');
        $this->assertEquals('123456789012', $result, 'Unexpected SKU result');

        $product = $this->getProduct();
        $sku = $product->getSku();
    
        $result = $repo->generateSKU($product);

        $this->assertInternalType('string', $result, 'Result is not string');
        $this->assertEquals($sku, $result, 'SKU result differs');

        $k = 11 > strlen($sku) ? 11 - strlen($sku) : 0;
        $this->assertEquals(strlen($sku) + $k, strlen($result), 'Unexpected strlen of SKU result');
    }

    /**
     * Test assembleUniqueSKU() method
     *
     * @dataProvider providerAssembleUniqueSKU
     */
	public function testAssembleUniqueSKU($sku, $expected = null)
    {
        $repo = $this->getRepo();

        $result = $repo->assembleUniqueSKU($sku);

        $this->assertInternalType('string', $result, 'Result is not string');

        if ($expected) {
            $this->assertEquals($expected, $result, 'Unexpected SKU result');

        } else {
            $this->assertRegExp('/^' . preg_quote($sku) . '\-[0-9a-z]+\.[0-9]+$/', $result, 'Unexpected SKU result');
        }
    }

    /**
     * Data provider for testAssembleUniqueSKU()
     */
    public function providerAssembleUniqueSKU()
    {
        $data = array(
            array('AA0123', 'AA0123'),
            array('123456789012', '123456789012'),
        );

        $product = $this->getProduct();

        if ($product) {
            $sku = $product->getSku();

            $data[] = array($sku);
        }

        return $data;
    }

    /**
     * Test getLowInventoryProductsAmount() method
     */
	public function testGetLowInventoryProductsAmount()
    {
        $repo = $this->getRepo();

        $result = $repo->getLowInventoryProductsAmount();

        $this->assertInternalType('int', $result, 'Result is not integer');
    }

    /**
     * Test search by substring
     */
    public function testSearchBySubstring()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();
        $substring = $product->getName();

        $cnd = $this->getSearchConditions();
        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = $substring;

        // Includes any

        foreach (array(\XLite\Model\Repo\Product::INCLUDING_ANY, \XLite\Model\Repo\Product::INCLUDING_ALL) as $inc) {

            $cnd->{\XLite\Model\Repo\Product::P_INCLUDING} = $inc;

            $result = $repo->search($cnd);

            $this->assertInternalType('array', $result, 'Result is not array');
            $this->assertNotEmpty($result, sprintf('Result is empty (substring: "%s")', $substring));

            $fields = array('name', 'description', 'sku');
            $substringFound = false;
            $productFound = false;
            foreach ($result as $r) {
                if ($product->getProductId() == $r->getProductId()) {
                    $productFound = true;
                }
                foreach ($fields as $f) {
                    $method = 'get' . \XLite\Core\Converter::convertToCamelCase($f);
                    if (preg_match('/' . preg_quote($substring) . '/', $r->$method())) {
                        $substringFound = true;
                        break;
                    }
                }
                $this->assertTrue($substringFound, sprintf('Product name, description and sku do not contains substring "%s"', $substring));
            }

            $this->assertTrue($productFound, sprintf('Etalon product not found by substring "%s"', $substring));

            $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = '"' . $substring . '"';
        }
    }

    /**
     * Test search by substring #2
     */
    public function testSearchBySubstring2()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();
        $substring = $product->getName();

        $cnd = $this->getSearchConditions();

        // E:0039975

        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = $substring;
        $cnd->{\XLite\Model\Repo\Product::P_INCLUDING} = '1<ScRiPt%20%0d%0a>alert(/xss15212/.source)%3B</ScRiPt>';
        $result = $repo->search($cnd, true);

        $this->assertGreaterThanOrEqual(1, $result, sprintf('There should be 0 products on substring "%s" and non-standard "includes" value', $substring));

        // Check for error exceptions
        $fields = $this->getSearchFields();

        foreach ($fields as $field) {

            if (in_array($field, array(\XLite\Model\Repo\Product::P_LIMIT, \XLite\Model\Repo\Product::P_ORDER_BY))) {
                $cnd->{$field} = array(
                    '1<ScRiPt%20%0d%0a>alert(/xss15212/.source)%3B</ScRiPt>',
                    '1<ScRiPt%20%0d%0a>alert(/xss15212/.source)%3B</ScRiPt>',
                );

            } else {
                $cnd->{$field} = '1<ScRiPt%20%0d%0a>alert(/xss15212/.source)%3B</ScRiPt>';
            }

            $result = $repo->search($cnd);

            $this->assertInternalType('array', $result, sprintf('Result is not array (%s)', $field));
        }
    }

    /**
     * Test search in single category
     */
    public function testSearchInSingleCategory()
    {
        $repo = $this->getRepo();

        $product = $this->getProduct();
        $categories = $product->getCategories();


        $catId = $categories[0]->getCategoryId();

        $cnd = $this->getSearchConditions();

        // Pass category ID
        $cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID} = $catId;

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, sprintf('Result is not array (category: %d)', $catId));

        foreach ($result as $r) {
            $rCategories = \Includes\Utils\ArrayManager::getObjectsArrayFieldValues($r->getCategories(), 'category_id', false);
            $this->assertContains($catId, $rCategories, sprintf('Found product (id: %d) does not belong the category (category: %d)', $r->getProductId(), $catId));
        }

        // Pass category object
        $cnd->{\XLite\Model\Repo\Product::P_CATEGORY_ID} = $categories[0];

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, sprintf('Result is not array (category: %d)', $catId));

        foreach ($result as $r) {
            $rCategories = \Includes\Utils\ArrayManager::getObjectsArrayFieldValues($r->getCategories(), 'category_id', false);
            $this->assertContains($catId, $rCategories, sprintf('Found product (id: %d) does not belong the category (category: %d)', $r->getProductId(), $catId));
        }
    }

    /**
     * Test search by 'enabled'
     */
    public function testSearchByEnabled()
    {
        $repo = $this->getRepo();

        $cnd = $this->getSearchConditions();
        $cnd->{\XLite\Model\Repo\Product::P_ENABLED} = true;

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, 'Result is not array');

        foreach ($result as $r) {
            $this->assertEquals(1, $r->getEnabled(), 'Found disabled product');
        }

        $cnd->{\XLite\Model\Repo\Product::P_ENABLED} = false;

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, 'Result is not array');

        foreach ($result as $r) {
            $this->assertEquals(0, $r->getEnabled(), 'Found disabled product');
        }
    }

    /**
     * Test search by price
     */
    public function testSearchByPrice()
    {
        $repo = $this->getRepo();

        $min = 5;
        $max = 20;

        $cnd = $this->getSearchConditions();
        $cnd->{\XLite\Model\Repo\Product::P_PRICE} = array($min, $max);

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, 'Result is not array');

        foreach ($result as $r) {
            $price = $r->getPrice();
            $this->assertTrue($price >= $min && $price <= $max, sprintf('Found product with price out of range (%f)', $price));
        }

    }

    /**
     * Test search by excluding product IDs
     *
     * @dataProvider providerSearchExcludingProductIds
     */
    public function testSearchExcludingProductIds($data)
    {
        $repo = $this->getRepo();

        $cnd = $this->getSearchConditions();
        $cnd->{\XLite\Model\Repo\Product::P_EXCL_PRODUCT_ID} = $data;
        $cnd->{\XLite\Model\Repo\Product::P_ORDER_BY} = array('p.sku', 'ASC');

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, 'Result is not array');

        foreach ($result as $r) {
            $this->assertNotContains($r->getProductId(), is_array($data) ? $data : array($data), 'Found excluded product');
        }

    }

    /**
     * Data provider for testSearchExcludingProductIds
     */
    public function providerSearchExcludingProductIds()
    {
        $product = $this->getProduct();
        $productId = $product->getProductId();

        return array(
            array(array($productId)),
            array($productId),
            array(array()),
            array(array($productId, 12)),
        );
    }

    /**
     * Test search by inventory status
     *
     * @dataProvider providerSearchByInventoryStatus
     */
    public function testSearchByInventoryStatus($status)
    {
        $repo = $this->getRepo();

        $cnd = $this->getSearchConditions();
        $cnd->{\XLite\Model\Repo\Product::P_INVENTORY} = $status;

        $result = $repo->search($cnd);

        $this->assertInternalType('array', $result, 'Result is not array');


        if ($status != \XLite\Model\Repo\Product::INV_ALL) {

            foreach ($result as $r) {

               $inv = $r->getInventory();

                if (!$inv->getEnabled()) {
                    continue;
                }

                $lowEnabled = $inv->getLowLimitEnabled();
                $amount = $inv->getAmount();
                $low = $inv->getLowLimitAmount();
                
                switch ($status) {
        
                    case \XLite\Model\Repo\Product::INV_LOW: {
                        $this->assertTrue($lowEnabled && $amount <= $low, 'Low limit products checking');
                        break;
                    }

                    case \XLite\Model\Repo\Product::INV_OUT: {
                        $this->assertTrue(0 >= $amount, 'Out of stock products checking');
                        break;
                    }

                    case \XLite\Model\Repo\Product::INV_IN: {
                        $this->assertTrue(0 <= $amount, 'In stock products checking');
                        break;
                    }

                    default:
                }
            }
        }
        
    }

    /**
     * Data provider for testSearchByInventoryStatus()
     */
    public function providerSearchByInventoryStatus()
    {
        return array(
            array(\XLite\Model\Repo\Product::INV_ALL),
            array(\XLite\Model\Repo\Product::INV_LOW),
            array(\XLite\Model\Repo\Product::INV_OUT),
            array(\XLite\Model\Repo\Product::INV_IN),
        );
    }

    // }}}

    // {{{ Service methods

    /**
     * Service method to get product model repository object
     */
    protected function getRepo()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product');
    }

    /**
     * Service method to get first product
     */
    protected function getProduct()
    {
        $products = $this->getRepo()->findAll();


        return $products[0];
    }

    /**
     * Get array of all fields allowable for searching
     */
    public function getSearchFields()
    {
        return array(
            \XLite\Model\Repo\Product::P_SKU,
            \XLite\Model\Repo\Product::P_CATEGORY_ID,
            \XLite\Model\Repo\Product::P_SUBSTRING,
            \XLite\Model\Repo\Product::P_SEARCH_IN_SUBCATS,
            \XLite\Model\Repo\Product::P_INVENTORY,
            \XLite\Model\Repo\Product::P_INCLUDING,
            \XLite\Model\Repo\Product::P_LIMIT,
            // The field below is disabled because of error generated by Doctrine:
            // Doctrine\ORM\Query\QueryException: [Syntax Error] line 0, col 301: Error: Expected end of string, got '<'
            // \XLite\Model\Repo\Product::P_ORDER_BY, 
        );
    }


    // }}}
}
