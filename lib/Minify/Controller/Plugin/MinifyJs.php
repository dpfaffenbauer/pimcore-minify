<?php

namespace Minify\Controller\Plugin;

use Pimcore\Tool;

class MinifyJs extends \Zend_Controller_Plugin_Abstract {

    protected $enabled = true;
    protected $conf;

    public function routeStartup(\Zend_Controller_Request_Abstract $request) {

        /*
$conf = Pimcore_Config::getSystemConfig();
        if (!$conf->outputfilters) {
            return $this->disable();
        }

        if (!$conf->outputfilters->javascriptminify) {
            return $this->disable();
        }

        $this->conf = $conf;
*/
    }

    public function disable() {
        $this->enabled = false;
        return true;
    }

    public function minify ($js) {

        try {
            //if($this->conf->outputfilters->javascriptminifyalgorithm == "jsminplus") {
            $js = \JSMinPlus::minify($js);
            /*
}
            else if ($this->conf->outputfilters->javascriptminifyalgorithm == "yuicompressor") {
                Minify_YUICompressor::$tempDir = PIMCORE_TEMPORARY_DIRECTORY;
                Minify_YUICompressor::$jarFile = PIMCORE_PATH . "/lib/Minify/yuicompressor-2.4.2.jar";
                $js = Minify_YUICompressor::minifyJs($js,array(
                    'charset'=>'utf8'
                ));
            }
            else {
                $js = JSMin::minify($js);
            }
*/
        }
        catch (\Exception $e) {
            \Logger::error("Unable to minify javascript");
            \Logger::error($e);
        }

        return $js;
    }

    public function dispatchLoopShutdown() {

        if(!Tool::isHtmlResponse($this->getResponse())) {
            return;
        }

        if(!Tool::useFrontendOutputFilters($this->getRequest()) && !$this->getRequest()->getParam("pimcore_preview")) {
            return;
        }

        if(\Pimcore::inDebugMode())
        {
            return;
        }

        if ($this->enabled) {
            include_once("simple_html_dom.php");

            $body = $this->getResponse()->getBody();
            $html = str_get_html($body);

            if($html) {
                $html = $this->searchForScriptSrcAndReplace($html);
                $html = $this->searchForInlineScriptAndReplace($html);

                $body = $html->save();

                $html->clear();
                unset($html);
            }

            $this->getResponse()->setBody($body);
        }
    }

    protected function searchForInlineScriptAndReplace(\simple_html_dom $html) {
        $inlineScripts = $html->find("script[!src]");
        $scriptsText = "";

        foreach($inlineScripts as $script)
        {
            if(substr(strtolower(strip_tags(($script->outertext))), 0, 13) != "window.jquery") {
                $scriptsText .= $script->makeup() . $this->minify($script->innertext) . '</script>';

                $script->outertext = substr(strtolower(strip_tags(($script->outertext))), 0, 13);
            }
        }

        $e = $html->find("body", 0);
        $e->outertext = $e->makeup() . $e->innertext . $scriptsText . '</body>';

        return $html;
    }

    protected function searchForScriptSrcAndReplace(\simple_html_dom $html) {

        $scripts = $html->find("script[src]");
        $scriptContent = "";
        $async = "";
        $prevAsync = "";

        foreach ($scripts as $script) {

            $source = $script->src;
            $async = $script->async ? $script->async : "false";
            $path = "";

            if (!preg_match("@http(s)?://@i", $source)) {
                if (@is_file("file://" . PIMCORE_ASSET_DIRECTORY . $source)) {
                    $path = "file://" . PIMCORE_ASSET_DIRECTORY . $source;
                } else if (@is_file("file://" . PIMCORE_DOCUMENT_ROOT . $source)) {
                    $path = "file://" . PIMCORE_DOCUMENT_ROOT . $source;
                }
            }

            // handle async attribute
            if (!empty($prevAsync) && $prevAsync != $async) {
                $scriptPath = $this->writeJsTempFile($scriptContent);
                $scriptContent = "";
                $script->prev_sibling()->outertext = '<script type="text/javascript" async="' . $prevAsync . '" src="' . str_replace(PIMCORE_DOCUMENT_ROOT, "", $scriptPath) . '"></script>' . "\n";
            }


            if ($path && @is_file($path)) {
                $scriptContent .= file_get_contents($path) . "\n\n";

                if ($script->next_sibling()->tag != "script" || !$script->next_sibling()->src) {
                    $scriptPath = $this->writeJsTempFile($scriptContent);
                    $scriptContent = "";
                    $script->outertext = '<script type="text/javascript" async="' . $async . '" src="' . str_replace(PIMCORE_DOCUMENT_ROOT, "", $scriptPath) . '"></script>' . "\n";
                } else {
                    $script->outertext = "";
                }
            } else if ($script->prev_sibling()->tag == "script") {

                if (strlen($scriptContent) > 0) {
                    $scriptPath = $this->writeJsTempFile($scriptContent);
                    $scriptContent = "";
                    $script->outertext = '<script type="text/javascript" async="' . $async . '" src="' . str_replace(PIMCORE_DOCUMENT_ROOT, "", $scriptPath) . '"></script>' . "\n" . $script->outertext;
                }
            }

            $prevAsync = $async;
        }

        return $html;
    }

    protected function writeJsTempFile ($scriptContent) {
        $scriptPath = PIMCORE_TEMPORARY_DIRECTORY."/minified_javascript_".md5($scriptContent).".js";

        if(!is_file($scriptPath)) {
            $scriptContent = ($this->minify($scriptContent));
            file_put_contents($scriptPath, $scriptContent);
            chmod($scriptPath, 0766);
        }
        return $scriptPath;
    }
}

