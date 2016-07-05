<?php

/**
 * Core plugin class
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/core
 */
class HLGroupsCore
{
    /** @var HLGroupsTemplate */
    protected $template;

    /** @var stdClass of plugin config */
    protected $plugin;
        
    protected function __construct()
    {
        $this->template = HLGroupsTemplate::getInstance();
        $this->plugin = $this->setPluginInfo();
    }

    /**
     * Create plugin info
     * @return stdClass of plugin info
     */
    private function setPluginInfo()
    {
        $config = new stdClass();
        $config->name = 'hl-fb-groups';
        $config->path = WP_PLUGIN_DIR . '/' . $config->name . '/';
        $config->mode = is_admin() ? 'admin' : 'public';
        
        return $config;
    }
    
    /**
     * Get array with scope class and method of this class
     * @param string $method
     * @param string|null $object
     * @return array
     */
    protected function getCallbackFuncByName($method, $object = null)
    {
        return [
            $object ? $this->{$object} : $this,
            $method
        ];
    }

    /**
     * initialization of plugin actions
     * @param string $part - admin or public part
     */
    protected function initActions($part)
    {
        foreach ($this->getConfig($part . '/actions') as $item) {
            add_action(
                $item->action,
                $this->getCallbackFuncByName($item->method, $item->scope),
                $item->priority,
                $item->accepted_args
            );
        }
    }

    /**
     * initialization of plugin filters
     * @param string $part - admin or public part
     */
    protected function initFilters($part)
    {
        foreach ($this->getConfig($part . '/filters') as $item) {
            add_filter(
                $item->filter,
                $this->getCallbackFuncByName($item->method, $item->scope),
                $item->priority,
                $item->accepted_args
            );
        }
    }

    /**
     * initialization of plugin short codes
     * @param string $part - admin or public part
     */
    protected function initShortCodes($part)
    {
        foreach ($this->getConfig($part . '/shortCodes') as $item) {
            add_shortcode(
                $item->name,
                $this->getCallbackFuncByName($item->method, $item->scope)
            );
        }
    }

    /**
     * Get plugin config by file name
     * @param string $name
     * @return array|mixed|object
     */
    protected function getConfig($name)
    {
        $file = $this->getConfigFilePath($name);

        return $file
            ? json_decode(file_get_contents($file))
            : [];
    }

    /**
     * Get file path, if file exists
     * @param string $name
     * @return null|string
     */
    private function getConfigFilePath($name)
    {
        $filePath = $this->plugin->path . 'config/' . $name . 'Config.json';

        return file_exists($filePath) 
            ? $filePath
            : null;
            
    }
}