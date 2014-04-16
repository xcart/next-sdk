<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Model\Repo;

/**
 * @coversDefaultClass \XLite\Model\Repo\Config
 */
class ConfigTest extends \XLiteUnit\Model\Repo\ARepo
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

        $this->assertEquals('service', $result, 'Unexpected repo type');
	}

    /**
     * Test assignDefaultOrderBy() method
     */
	public function testAssignDefaultOrderBy()
    {
        $repo = $this->getRepo();

        $qb = $this->getQueryBuilder();

        $result = $repo->assignDefaultOrderBy($qb, 'q');

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $result, 'Result is not \Doctrine\ORM\QueryBuilder object');

        $this->assertRegExp('/q.orderby ASC/', $qb->getDql(), 'Order by is not found in DQL');
	}

    /**
     * Test createQueryBuilder() method
     */
    public function testCreateQueryBuilder()
    {
        $repo = $this->getRepo();

        $result = $repo->createQueryBuilder('q');

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $result, 'Result is not \Doctrine\ORM\QueryBuilder object');

        $this->assertRegExp('/q.orderby ASC/', $result->getDql(), 'Order by is not found in DQL');
    }

    /**
     * Test createPureQueryBuilder() method
     */
	public function testCreatePureQueryBuilder()
    {
        $repo = $this->getRepo();

        $result = $repo->createPureQueryBuilder('q');

        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $result, 'Result is not \Doctrine\ORM\QueryBuilder object');

        $this->assertNotRegExp('/q.orderby ASC/', $result->getDql(), 'Order by is found in DQL');
	}

    // }}}

    /**
     * Service method to get product model repository object
     */
    protected function getRepo()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Config');
    }
}
