<?php

/**
 * Plugin config file
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/config
 */
abstract class HLGroupsConfig
{
    const name            = 'hl-fb-groups';
    const userGroupType   = 'fb_group';
    const userPostsType   = 'fb_post';
    const publicGroupType = 'fb_group_public';
    const facebookApi     = 'https://graph.facebook.com/v2.6/';
    const facebookScope   = 'email,public_profile,user_managed_groups,publish_actions';
    
    /**
     * Get config entity by name
     * @param string $param
     * @return string
     */
    protected function config($param)
    {
        if (defined('self::' . $param)) {
            return constant('self::' . $param);
        }

        if (method_exists($this, $param)) {
            return call_user_func([$this, $param]);
        }
        
        return '';
    }

    /**
     * Get plugin path
     * @return string
     */
    private function path()
    {
        return WP_PLUGIN_DIR . '/' . self::name . '/';
    }

    /**
     * Get plugin path
     * @return string
     */
    private function publicUrl()
    {
        return plugins_url() . '/' . self::name . '/' . $this->currentMode();
    }

    /**
     * Get current plugin mode
     * @return string
     */
    private function currentMode()
    {
        return !is_admin() ? 'public' : 'admin';
    }
    
    private function currentPath()
    {
        return WP_PLUGIN_DIR . '/' . self::name . '/' . $this->currentMode() . '/';
    }

    /**
     * Get config folder path
     * @return string
     */
    private function configFolder()
    {
        return $this->path() . 'config/';
    }

    /**
     * Get app id from Wordpress setting
     * @return string
     */
    private function facebookAppId()
    {
        return get_option('fbl_settings')["fb_id"];
    }
}