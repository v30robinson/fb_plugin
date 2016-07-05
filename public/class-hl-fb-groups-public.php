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

        $this->initActions($this->plugin->mode);
        $this->initShortCodes($this->plugin->mode);
    }

    /**
     * Save user groups and posts to Wordpress DB as custom post type
     */
    public function saveFacebookGroups()
    {
        $customPostType = new HLGroupsFacebookManager();
        $customPostType->loadFacebookGroups();
    }

    /**
     * Display user groups and post for groups
     */
    public function displayUserGroups()
    {
        $entities = new HLGroupsLocalManager();
        
        $this->template->render('groups-list', [
            'groups'  => $entities->getGroupEntities(get_current_user_id()),
            'formUrl' => $_SERVER['REQUEST_URI']
        ]);
    }

    /**
     * Display public groups and form for creating new one group
     * in the local storage
     */
    public function displayPublicGroups()
    {
        $entities = new HLGroupsLocalManager();
        
        $this->template->render('public-groups-list', [
            'groups'      => $entities->getPublicGroupEntities(),
            'groupsCount' => $entities->countOfPublicGroupPages(),
            'formUrl'     => $_SERVER['REQUEST_URI'],
            'user'        => get_current_user_id()
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