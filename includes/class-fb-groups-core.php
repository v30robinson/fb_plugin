<?php

/**
 * Core plugin class
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/core
 */
class FBGroupsCore extends FBGroupsConfig
{
    /** @var FBGroupsTemplate */
    protected $template;
    
    /** @var FBGroupsFacebookManager */
    protected $facebookManager;

    /** @var FBGroupsLocalManager */
    protected $localEntityManager;
    
    /** @var FBGroupsForm */
    protected $formManager;
    
    protected function __construct()
    {
        $this->template           = FBGroupsTemplate::getInstance();
        $this->formManager        = new FBGroupsForm();
        $this->facebookManager    = new FBGroupsFacebookManager();
        $this->localEntityManager = new FBGroupsLocalManager();
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
                $this->getCallbackFuncByName($item->method . 'Action', $item->scope),
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
                $this->getCallbackFuncByName($item->method . 'Filter', $item->scope),
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
                $this->getCallbackFuncByName($item->method . 'ShortCode', $item->scope)
            );
        }
    }

    /**
     * initialization of plugin scritps
     * @param string $part - admin or public part
     */
    protected function initScripts($part)
    {
        foreach ($this->getConfig($part . '/scripts') as $key => $scripts) {
            $this->template->insertScript(
                $key,
                $this->config('publicUrl') . '/js/' . $scripts->name . '.js',
                $scripts->dependency
            );
        }
    }

    /**
     * Create menu page in the admin area
     * @param object $item
     */
    protected function initMenuPage($item)
    {
        add_menu_page(
            null,
            $item->name,
            'manage_options',
            $item->slug,
            $this->getCallbackFuncByName($item->method . 'Action', $item->scope),
            $item->icon,
            $item->position
        );
    }

    /**
     * Create submenu page in the admin area
     * @param object $item
     */
    protected function initSubMenuPage($item)
    {
        add_submenu_page(
            $item->parent,
            $item->pageTitle,
            $item->menuTitle, 
            'manage_options',
            $item->slug,
            $this->getCallbackFuncByName($item->method . 'Action', $item->scope)
        );
    }

    /**
     * initialization of plugin post types
     */
    protected function initPostTypes()
    {
        foreach ($this->getConfig('postType') as $key => $postType) {
            register_post_type($key, [
                'labels'       => $postType->labels,
                'public'       => $postType->public,
                'has_archive'  => $postType->has_archive,
                'supports'     => $postType->supports,
                'show_in_menu' => $postType->adminMenu
            ]);
        }
    }

    /**
     * Check request for must have params
     * @param array $params
     * @return bool
     */
    protected function checkRequest($params = [])
    {
        foreach ($params as $param) {
            if (!array_key_exists($param, $_REQUEST)) {
                return false;
            }
        }
        return true;
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
        $filePath = $this->config('configFolder') . $name . 'Config.json';

        return file_exists($filePath) 
            ? $filePath
            : null;
    }
}