<?php

namespace Minify;

use Pimcore\API\Plugin\AbstractPlugin;
use Pimcore\API\Plugin\PluginInterface;

class Plugin extends AbstractPlugin implements PluginInterface
{
    public function preDispatch($e)
    {
        $e->getTarget()->registerPlugin(new Controller\Plugin\MinifyCss(), 10000);
        $e->getTarget()->registerPlugin(new Controller\Plugin\MinifyJs(), 10001);

        include_once(PIMCORE_PLUGINS_PATH . '/Minify/vendor/autoload.php');
    }

    /**
     * @return string $statusMessage
     */
    public static function install() {
        return "";
    }

    /**
     * @return boolean $isInstalled
     */
    public static function isInstalled() {
        return true;
    }

    /**
     * @return string $statusMessage
     */
    public static function uninstall() {
        return "";
    }

    /**
     * @return boolean $needsReloadAfterInstall
     */
    public static function needsReloadAfterInstall() {
        return false;
    }
}
