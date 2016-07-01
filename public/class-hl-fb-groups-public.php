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

        add_action('wp', [$this, 'initPublicLibs']);
        add_action('fbl/after_login', [$this, 'saveFacebookGroups']);
        add_action('parse_request', [$this, 'getPostForm']);
        add_shortcode('get-user-groups', [$this, 'displayUserGroups']);
    }

    /**
     * Insert styles and js files to user page
     */
    public function initPublicLibs()
    {
        $this->template->insertJSAndStyle();
    }

    /**
     * Save user groups and posts to Wordpress DB as custom post type
     */
    public function saveFacebookGroups()
    {
        $token = $this->getUserToken();
        $this->saveLocalToken($token);

        $customPostType = new HLGroupsFacebookManager($token);
        $customPostType->loadFacebookGroups();
    }

    /**
     * Display user groups and post for groups
     */
    public function displayUserGroups()
    {
        $customPostType = new HLGroupsLocalEntityManager();
        $this->template->render('group-list', [
            'groups'  => $customPostType->getGroupEntities(get_current_user_id()),
            'formUrl' => $_SERVER['REQUEST_URI']
        ]);
    }

    public function getPostForm()
    {
        if (array_key_exists('fb-group-id', $_REQUEST) && array_key_exists('fb-group-post', $_REQUEST)) {
            $facebookManager = new HLGroupsFacebookManager($this->getUserToken());
            $facebookManager->pushFacebookPost(
                $_REQUEST['fb-group-id'],
                $_REQUEST['fb-group-post']
            );
        }        
    }
}