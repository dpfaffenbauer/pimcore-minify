<?php


class Minify_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {
    
	protected static $installedFileName = "/var/config/.minify";

    public static function isInstalled()
    {
        return file_exists(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public function preDispatch($e)
    {
        $e->getTarget()->registerPlugin(new Minify_Controller_Plugin_MinifyCss(), 1000);
        $e->getTarget()->registerPlugin(new Minify_Controller_Plugin_MinifyJs(), 1001);
        
        include_once(PIMCORE_PLUGINS_PATH . '/Minify/vendor/autoload.php');
    }

    public static function install()
    {
        touch(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public static function uninstall()
    {
        unlink(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
}
