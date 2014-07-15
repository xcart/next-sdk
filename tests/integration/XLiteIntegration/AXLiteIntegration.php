<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteIntegration;

/**
 *  Abstract unit test case
 */
abstract class AXLiteIntegration extends \XLiteUnit\AXLiteUnit //\XLiteTest\Framework\TestCase
{
    /**
     * Service method: create SQL dump
     *
     * @return boolean
     */
    public static function createDump($path = null)
    {
        // DB backup
        echo (PHP_EOL . 'DB backup ... ');

        $result = true;

        if (!isset($path)) {
            $path = __DIR__ . '/../files/dump.sql';
        }

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path));
        }

        if (file_exists(dirname($path))) {

            if (file_exists($path)) {
                unlink($path);
            }

            $config = \XLite::getInstance()->getOptions('database_details');

            $cmd = defined('TEST_MYSQLDUMP_BIN') ? TEST_MYSQLDUMP_BIN : 'mysqldump';
            $cmd .= ' --opt -h' . $config['hostspec'];

            if ($config['port']) {
                $cmd .= ' -P' . $config['port'];
            }

            $cmd .= ' -u' . $config['username'] . ('' == $config['password'] ? '' : (' -p' . $config['password']));

            if ($config['socket']) {
                $cmd .= ' -S' . $config['socket'];
            }

            exec($cmd .= ' ' . $config['database'] . ' > ' . $path);

            echo ('done' . PHP_EOL);

            sleep(1);

        } else {
            $result = false;
        }

        if (!$result) {
            echo ('ignored' . PHP_EOL);
        }

        return $result;
    }

    /**
     * Service method: restore from SQL dump
     *
     * @return boolean
     */
    public static function restoreFromDump($path = null, $verbose = true, $drop = true, &$message = null)
    {
        !$verbose && ob_start();

        echo (PHP_EOL . 'DB restore ... ');

        $result = true;

        if (!isset($path)) {
            $path = __DIR__ . '/../files/dump.sql';
        }

        if (file_exists($path)) {

            $config = \XLite::getInstance()->getOptions('database_details');

            $cmd = defined('TEST_MYSQL_BIN') ? TEST_MYSQL_BIN : 'mysql';
            $cmd .= ' -h' . $config['hostspec'];

            if ($config['port']) {
                $cmd .= ' -P' . $config['port'];
            }

            $cmd .= ' -u' . $config['username'] . ('' == $config['password'] ? '' : (' -p' . $config['password']));

            if ($config['socket']) {
                $cmd .= ' -S' . $config['socket'];
            }

            $message = '';

            if ($drop) {

                // Drop&Create database

                exec($cmd . ' -e"drop database ' . $config['database'] . '"' , $message);

                if (empty($message)) {
                    exec($cmd . ' -e"create database ' . $config['database'] . '"', $message);
                }
            }

            if (empty($message)) {
                exec($cmd . ' ' . $config['database'] . ' < ' . $path, $message);
            }

            if (empty($message)) {
                echo ('done' . PHP_EOL);

            } else {
                $result = false;
                echo ('failed: ' . $message . PHP_EOL);
            }

        } else {
            echo ('ignored (sql-dump file not found)' . PHP_EOL);
            $result = false;
        }

        !$verbose && ob_end_clean();

        return $result;
    }
}
