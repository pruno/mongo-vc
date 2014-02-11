<?php
namespace MongovcTests;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Class Bootstrap
 * @package MongovcTests
 */
class Bootstrap
{
    /**
     * @var array
     */
    protected static $config;

    public static function init()
    {
        static::loadConfig();
        static::initAutoloader();

        /**
         * Start output buffering, if enabled
         */
        if (defined('TESTS_ZEND_OB_ENABLED') && constant('TESTS_ZEND_OB_ENABLED')) {
            ob_start();
        }
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * @throws \RuntimeException
     */
    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        }

        $zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/zendframework/zendframework/library') ? $vendorPath . '/zendframework/zendframework/library' : false));

        if (!$zf2Path) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
        }

        if (isset($loader)) {
            $loader->add('Zend', $zf2Path . '/Zend');
        } else {
            include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
            AutoloaderFactory::factory(array(
                'Zend\Loader\StandardAutoloader' => array(
                    'autoregister_zf' => true,
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                    ),
                ),
            ));
        }
    }

    /**
     * @param string $path
     * @return bool|string
     */
    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

    protected static function loadConfig()
    {
        if (is_readable(__DIR__ . '/testConfig.php')) {
            self::$config = include __DIR__ . '/testConfig.php';
        } else {
            self::$config = include __DIR__ . '/testConfig.php.dist';
        }

        if (!is_array(self::$config)) {
            throw new RuntimeException("Expecting array as configuration ".gettype(self::$config)." given.");
        }
    }
}

Bootstrap::init();