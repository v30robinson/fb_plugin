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

        $this->initActions();
        $this->initShortCodes();
    }

    /**
     * initialization of plugin actions
     */
    private function initActions()
    {
        add_action('wp', [$this, 'initPublicLibs']);
        add_action('fbl/after_login', [$this, 'saveFacebookGroups'], 10, 2);
        add_action('parse_request', [$this, 'parseAllForms']);
    }

    /**
     * initialization of plugin short codes system
     */
    private function initShortCodes()
    {
        add_shortcode('user-personal-groups', [$this, 'displayUserGroups']);
        add_shortcode('public-groups', [$this, 'displayPublicGroups']);
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
        
        $this->template->render('groups-list', [
            'groups'  => $customPostType->getGroupEntities(get_current_user_id()),
            'formUrl' => $_SERVER['REQUEST_URI']
        ]);
    }

    /**
     * Display public groups and form for creating new one group
     * in the local storage
     */
    public function displayPublicGroups()
    {
        $customPostType = new HLGroupsLocalEntityManager();

        $this->template->render('public-groups-list', [
            'groups'  => $customPostType->getGroupEntities(0),
            'formUrl' => $_SERVER['REQUEST_URI']
        ]);
    }

    /**
     * Parser for all user form (for FB post and FB groups)
     */
    public function parseAllForms()
    {
        $form = new HLGroupsForm();
        $form->parseUserPostFrom($_REQUEST);
        $form->parsePublicGroupForm($_REQUEST);
    }
}