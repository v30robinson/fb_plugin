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
    protected function __construct() { }

    /**
     * get user token from request
     * @return string
     */
    protected function getUserToken()
    {
        return isset($_POST['fb_response']['authResponse']['accessToken'])
            ? $_POST['fb_response']['authResponse']['accessToken']
            : '';
    }
}