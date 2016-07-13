<?php

/**
 * Class for help work with templates
 * Can find template by name and show on the page
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/template
 */
class FBGroupsTemplate extends FBGroupsConfig
{
    /** @var $instance */
    private static $instance;
    
    /** @var Twig_Environment $twig class */
    private $twig;
    
    /**
     * Get instance of the class
     * @return FBGroupsTemplate
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FBGroupsTemplate();
        }

        return self::$instance;
    }

    /**
     * Register the stylesheets and js for the admin area.
     */
    public function insertJSAndStyleAction()
    {
        $this->insertStyles();
        $this->insertFBLoginFix();
    }

    /**
     * Insert styles to the page
     */
    private function insertStyles()
    {
        wp_enqueue_style(
            'fb-groups-style',
            $this->config('publicUrl') . '/css/fb-groups.css'
        );
    }

    /**
     * Insert script to the page
     * @param $id
     * @param $path
     * @param array $dependency
     */
    public function insertScript($id, $path, $dependency = [])
    {
        wp_enqueue_script('fb-groups-script-' . $id,  $path, $dependency);
    }

    /**
     * Insert fix for Facebook Login plugin
     */
    private function insertFBLoginFix()
    {
        wp_register_script('some_handle', null);
        wp_enqueue_script ('some_handle');
        wp_localize_script('some_handle', 'fbl', [
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'site_url' => home_url(),
            'scopes'   => $this->config('facebookScope'),
            'appId'    => $this->config('facebookAppId')
        ]);
    }

    /**
     * Display html template
     * @param $template
     * @param array $replacement
     */
    public function render($template, $replacement = [])
    {
        echo $this->getTwig()->render($template . '.html', $replacement);
    }

    /**
     * Initialization twig engine and return Twig object
     * @return Twig_Environment
     */
    private function getTwig()
    {
        if (!$this->twig) {
            $loader     = new Twig_Loader_Filesystem($this->config('currentPath') . 'template/');
            $this->twig = new Twig_Environment($loader, [
                'cache' => $this->config('currentPath') . 'template/cache/',
            ]);
        }
        return $this->twig;
    }

    /**
     * FBMembershipTemplate disable clone
     */
    private function __clone() {}
}