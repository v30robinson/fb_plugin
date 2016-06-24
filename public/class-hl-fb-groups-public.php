<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/public
 */
class HLGroupsPublic extends HLGroupsCore
{
    /**
     * HLGroupsPublic constructor.
     */
    public function __construct()
    {   
        parent::__construct();
        add_action('fbl/after_login', [$this, 'saveFacebookGroups']);
    }

    /**
     * Save user groups and posts to Wordpress DB as custom post type
     */
    public function saveFacebookGroups()
    {   
        $customPostType = new HLGroupsCustomPosts($this->getUserToken());
        $customPostType->loadFacebookGroups();
    }
}