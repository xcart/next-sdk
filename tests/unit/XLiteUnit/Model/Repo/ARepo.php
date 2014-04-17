<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Model\Repo;

/**
 * Abstract class for Repo test class
 */
abstract class ARepo extends \XLiteUnit\AXLiteUnit
{
    /**
     * Service method to get search conditions object
     */
    protected function getSearchConditions()
    {
        return new \XLite\Core\CommonCell();
    }

    /**
     * Service method to get Doctrine query builder
     */
    protected function getQueryBuilder()
    {
        return $this->getRepo()->createQueryBuilder();
    }
}
