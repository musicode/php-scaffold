<?php

namespace App\Component;

use Smarty;

class SmartyView {

    public $templateDirectory = null;

    public $compileDirectory = null;

    public $cacheDirectory = null;

    public $pluginsDir = array();

    private $instance = null;

    public function __construct($templateDirectory, $compileDirectory, $cacheDirectory, $pluginsDir = null) {
        $this->templateDirectory = $templateDirectory;
        $this->compileDirectory = $compileDirectory;
        $this->cacheDirectory = $cacheDirectory;
        $this->pluginsDir = $pluginsDir;
    }

    public function render($template, $data = null) {
        $instance = $this->getInstance();
        $instance->assign($data);
        return $instance->fetch($template);
    }

    private function getInstance() {
        $instance = $this->instance;
        if (!($instance instanceof Smarty)) {
            $instance = new Smarty();
            $instance->left_delimiter = '{{ ';
            $instance->right_delimiter = ' }}';
            $instance->template_dir = $this->templateDirectory;
            if ($this->pluginsDir) {
                $instance->addPluginsDir($this->pluginsDir);
            }
            if ($this->compileDirectory) {
                $instance->compile_dir = $this->compileDirectory;
            }
            if ($this->cacheDirectory) {
                $instance->cache_dir = $this->cacheDirectory;
            }
            $this->instance = $instance;
        }
        return $this->instance;
    }
}