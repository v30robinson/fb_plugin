<?php
/**
 * The plugin bootstrap file.
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:    HealersLibrary Facebook Groups Plugin
 * Plugin URI:     https://bitbucket.org/mev/healerslibrary-facebook-plugin
 * Description:    Plugin for work with Facebook user groups
 * Version:        1.0
 * Author:         Stanislav Vysotskyi and Nick Temple
 * Author URI:     http://www.healerslibrary.com
 * License:        GPL-2.0+
 * License URI:    http://www.mev.com/license.txt
 * Text Domain:    hl-fb-groups
 *
 */

class HLGroups
{

    /**
     * Check, is plugin installed
     * @return bool
     */
    private function isInstalled()
    {
        return file_exists(plugin_dir_path( __FILE__ ) . 'vendor/autoload.php');
    }

    /**
     * Check plugin dependency
     * @return bool
     */
    private function checkPluginDependency()
    {
        if (!is_plugin_active('wp-facebook-login/facebook-login.php')) {
            echo '<b>Dependency error!</b> 
              For plugin work need to install and activate 
              <a href="https://wordpress.org/plugins/wp-facebook-login/" target="_blank">Facebook Login</a> plugin!
             ';
            return false;
        }
        return true;
    }

    /**
     * Check composer dependency
     * @return bool
     */
    private  function checkComposerDependency()
    {
        if (!defined('HL_COMPOSER')) {
            echo '<b>Composer error!</b> 
              You need to add <b>HL_COMPOSER</b> const to the WordPress config with path to composer file. It\'s needed 
              for installing plugin dependency (twig and etc.)
              ';
            return false;
        }
        return true;
    }

    /**
     * The code that runs during plugin activation.
     */
    public function activate_hl_fb_groups()
    {
        if ($this->checkPluginDependency() && $this->checkComposerDependency()) {
            putenv("COMPOSER_HOME=" . HL_COMPOSER . '.composer');
            exec('(cd ' . plugin_dir_path(__FILE__) .' && php ' . HL_COMPOSER . 'composer install) 2>&1');
            return;
        }
        exit();
    }

    /**
     * The code that runs during plugin deactivation.
     */
    public function deactivate_hl_fb_groups()
    {
        exec('rm -rfv ' . plugin_dir_path(__FILE__) . 'vendor/*');
    }

    /**
     * The core plugin classes that is used to define internationalization, admin-specific hooks,
     * and public-facing site hooks.
     */
    private function includeClasses()
    {
        require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-hl-fb-groups-config.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-hl-fb-groups-core.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-entity-manager.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-facebook-manager.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-local-manager.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-from.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-request.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/modules/class-hl-groups-template.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/class-hl-fb-groups-admin.php';
        require_once plugin_dir_path( __FILE__ ) . 'public/class-hl-fb-groups-public.php';
    }

    /**
     * Init core lib and setup activate/deactivate setting
     */
    public function initCore()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        register_activation_hook( __FILE__, [$this, 'activate_hl_fb_groups']);
        register_deactivation_hook( __FILE__, [$this, 'deactivate_hl_fb_groups']);
    }

    /**
     * Begins execution of the plugin.
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     */
    public function run()
    {
        if ($this->isInstalled() && is_plugin_active('wp-facebook-login/facebook-login.php')) {
            $this->includeClasses();
            $plugin = !is_admin() ? new HLGroupsPublic() : new HLGroupsAdmin();
        }
    }
}

if (defined('WPINC')) {
    $plugin = new HLGroups();
    $plugin->initCore();
    $plugin->run();
}