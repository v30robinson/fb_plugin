<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/admin
 */
class HLGroupsAdmin extends HLGroupsCore
{

    public function __construct()
    {
        parent::__construct();
        
        $this->initActions($this->config('currentMode'));
        $this->initFilters($this->config('currentMode'));
    }

    /**
     * @param WP_User $user
     * @param int $userId
     */
    public function updateCurrentUserAction($user, $userId)
    {
        wp_set_current_user($userId);
    }
    
    /**
     * Save user groups and posts to Wordpress DB as custom post type;
     * Set current user (fix for Facebook Login plugin)
     */
    public function saveFacebookGroupsAction()
    {
        $this->facebookManager->loadFacebookGroups();
    }

    /**
     * Add new columns for default Wordpress list of posts.
     * @param $columns
     * @return mixed
     */
    public function customPostListColumnsFilter($columns)
    {
        return array_merge(
            $columns, [
                'group'     => 'Group',
                'published' => 'Published data'
            ]
        );
    }
    
    /**
     * Init endpoint for ajax checking Facebook group;
     * send json this group info.
     */
    public function checkGroupEndpointAction()
    {
        if (array_key_exists('id', $_REQUEST)) {
            wp_send_json(
                $this->facebookManager->loadFacebookGroupInfo($_REQUEST['id'])
            );
        }
    }

    /**
     * Init endpoint for ajax loading more Facebook groups;
     * send json with groups list.
     */
    public function loadMoreEndpointAction()
    {
        if (array_key_exists('number', $_REQUEST)) {
            wp_send_json(
                $this->localEntityManager->getPublicGroupEntities($_REQUEST['number'])
            );
        }
    }

    /**
     * Init endpoint for ajax search Facebook groups;
     * send json with groups list.
     */
    public function searchGroupsAction()
    {
        if (array_key_exists('search', $_REQUEST)) {
            $nextCode       = array_key_exists('after', $_REQUEST) ? $_REQUEST['after'] : null;
            $facebookGroups = $this->facebookManager->findFacebookGroups($_REQUEST['search'], $nextCode);
            $groupsId       = array_column($facebookGroups['data'], 'id');
            $localEntities  = $this->localEntityManager->findLocalGroupsByIds($groupsId);
            wp_send_json($this->localEntityManager->mergeLocalGroup($facebookGroups, $localEntities));
        }
    }

    /**
     * Init endpoint for ajax adding public Facebook group.
     */
    public function addPublicGroupEndpointAction()
    {
        if (current_user_can('manage_options')) {
            $this->formManager->parsePublicGroupForm($_REQUEST);
        }
    }

    /**
     * Create menu item in the admin area
     */
    public function createWidgetMenuAction()
    {
        foreach ($this->getConfig('admin/menus') as $item) {
            $this->initMenuPage($item);
        }
    }

    /**
     * Create facebook widget page in the admin area
     */
    public function createWidgetPageAction()
    {
        $this->template->render('group-search');
    }

    /**
     * Register the stylesheets and js for the admin area.
     */
    public function adminLibsAction()
    {
        $this->template->insertJSAndStyleAction();
    }

    /**
     * initialization custom post types
     */
    public function initPostTypesAction()
    {
        $this->initPostTypes();
    }

    /**
     * Edit current Wordpress list for Facebook Posts
     * @param $column - current column for editing
     * @param $postId
     */
    public function customPostListAction($column, $postId)
    {
        $parent   = wp_get_post_parent_id($postId);
        $postMeta = unserialize(get_post_meta($postId, 'fb_post_data', true));
        $date     = new DateTime($postMeta['updated_time']);

        switch ($column) {
            case 'group':
                $groupTitle = get_the_title($parent);
                echo '<a href="/wp-admin/post.php?post=' . $parent . '&action=edit"> ' . $groupTitle . '</a>';
                break;

            case 'published':
                $date = human_time_diff($date->getTimestamp(), time());
                echo '<abbr title> ' . $date . '</abbr>';
                break;
        }
    }
}