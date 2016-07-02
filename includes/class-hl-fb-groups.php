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
    /** @var HLGroupsTemplate  */
    protected $template;
    
    protected function __construct()
    {
        $this->template = HLGroupsTemplate::getInstance();
    }

    /**
     * get user token from request or from local storage
     * @return string
     */
    protected function getUserToken()
    {        
        return isset($_POST['fb_response']['authResponse']['accessToken'])
            ? $_POST['fb_response']['authResponse']['accessToken']
            : get_user_meta(get_current_user_id(), 'fb-token', true);
    }

    /**
     * Save user token to local storage
     * @param $token
     */
    protected function saveLocalToken($token)
    {
        if (!empty($token)) {
            update_user_meta(get_current_user_id(), 'fb-token', $token);
        }
    }
}