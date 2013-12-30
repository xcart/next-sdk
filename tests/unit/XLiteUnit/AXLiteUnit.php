<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit;

/**
 *  Abstract unit test case
 */
abstract class AXLiteUnit extends \XLiteTest\Framework\TestCase
{
	/**
	 * Get original class-test-target
	 * 
	 * @return string
	 */
	static public function getOriginalClass()
	{
		$parts = explode('\\', get_called_class());
		$parts[0] = 'XLite';
        $parts[count($parts) - 1] = preg_replace('/Test$/Ss', '', $parts[count($parts) - 1]);

		return implode('\\', $parts);
	}

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
		parent::setUpBeforeClass();

		class_exists(static::getOriginalClass());
	}
}
