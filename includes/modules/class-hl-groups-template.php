<?php

/**
 * Class for help work with templates
 * Can find template by name and show on the page
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/template
 */
class HLGroupsTemplate
{
    /** @var $instance */
    private static $instance;

    /** @var array */
    private $config;

    /** @var Twig_Environment $twig class */
    private $twig;

    /**
     * HLMembershipTemplate constructor
     * Setup config for work this twig engine
     */
    private function __construct()
    {
        $pluginType   = is_admin() ? 'admin' : 'public';
        $this->config = new stdClass();

        $this->config->pluginPath = WP_PLUGIN_DIR . '/hl-fb-groups/' . $pluginType;
        $this->config->pluginUrl  = plugins_url() . '/hl-fb-groups/' . $pluginType;
    }
    
    /**
     * Get instance of the class
     * @return HLGroupsTemplate
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new HLGroupsTemplate();
        }

        return self::$instance;
    }

    /**
     * Register the stylesheets and js for the admin area.
     */
    public function insertJSAndStyle()
    {
        $this->insertStyles();
        $this->insertScript();
    }

    /**
     * Insert styles to the page
     */
    private function insertStyles()
    {
        wp_enqueue_style(
            'hl-fb-groups-style',
            $this->config->pluginUrl . '/css/hl-fb-groups.css'
        );
    }

    /**
     * Insert scripts to the page
     */
    private function insertScript()
    {
        wp_register_script('some_handle', null);
        wp_enqueue_script('some_handle');
        wp_localize_script('some_handle', 'fbl', [
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'site_url' => home_url(),
            'scopes'   => 'email,public_profile,user_managed_groups,publish_actions',
            'appId'    => get_option('fbl_settings')["fb_id"]
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
            $loader     = new Twig_Loader_Filesystem($this->config->pluginPath . '/template/');
            $this->twig = new Twig_Environment($loader, [
                'cache' => $this->config->pluginPath . '/template/cache/',
            ]);
        }
        return $this->twig;
    }

    /**
     * HLMembershipTemplate disable clone
     */
    private function __clone() {}
}