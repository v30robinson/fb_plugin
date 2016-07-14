<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/admin
 */
class FBGroupsAdmin extends FBGroupsCore
{

    public function __construct()
    {
        parent::__construct();

        $this->initActions($this->config('currentMode'));
        $this->initFilters($this->config('currentMode'));
    }

    /**
     * Init endpoint for ajax getting user posts;
     * send json with posts list.
     */
    public function getGroupPostsAction()
    {
        if ($this->checkRequest(['groupId', 'offset'])) {
            wp_send_json(
                $this->facebookManager->getFacebookPosts($_REQUEST['groupId'], null, $_REQUEST['offset'])
            );
        }
    }

    /**
     * Init endpoint for ajax checking Facebook group;
     * send json this group info.
     */
    public function checkGroupEndpointAction()
    {
        if ($this->checkRequest(['id'])) {
            wp_send_json(
                $this->facebookManager->loadFacebookGroupInfo($_REQUEST['id'])
            );
        }
    }

    /**
     * Init endpoint for ajax deleting local groups;
     * send json with groups list.
     */
    public function deleteLocalGroupAction()
    {
        if ($this->checkRequest(['groupId'])) {
            wp_send_json(
                $this->localEntityManager->deleteLocalEntityById($_REQUEST['groupId'])
            );
        }
    }

    /**
     * Init endpoint for ajax search local groups;
     * send json with groups list.
     */
    public function getPublicGroupsAction()
    {
        if ($this->checkRequest(['search', 'offset'])) {
            wp_send_json(
                $this->localEntityManager->getPublicGroupEntities($_REQUEST['offset'], 6, $_REQUEST['search'])
            );
        }
    }

    /**
     * Init endpoint for ajax search Facebook groups;
     * send json with groups list.
     * @todo need refactoring
     */
    public function searchGroupsAction()
    {
        if ($this->checkRequest(['search'])) {
            $nextCode = array_key_exists('after', $_REQUEST) ? $_REQUEST['after'] : null;
            $facebookGroups = $this->facebookManager->findFacebookGroups($_REQUEST['search'], $nextCode);
            $groupsId = array_column($facebookGroups['data'], 'id');
            $localEntities = $this->localEntityManager->findLocalGroupsByIds($groupsId);
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
     * @param WP_User $user
     * @param int $userId
     */
    public function updateCurrentUserAction($user, $userId)
    {
        wp_set_current_user($userId);
    }

    /**
     * Save user groups and posts to Wordpress DB as custom post type;
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
                'group' => 'Group',
                'published' => 'Published data'
            ]
        );
    }

    /**
     * Create menu item in the admin area
     */
    public function createWidgetMenuAction()
    {
        foreach ($this->getConfig('admin/menus') as $menu) {
            $this->initMenuPage($menu);
            foreach ($menu->subMenus as $subMenu) {
                $this->initSubMenuPage($subMenu);
            }
        }
    }

    /**
     * Display public facebook group search
     */
    public function publicSearchAction()
    {
        $this->template->render('group-search', [
            'token' => get_user_meta(get_current_user_id(), 'fb-token', true)
        ]);
    }

    /**
     * Display local storage
     */
    public function localStorageAction()
    {
        $this->template->render('local-storage', [
            'groups' => $this->localEntityManager->getPublicGroupEntities(0, 100000)
        ]);
    }
    
    /**
     * Register the stylesheets and js for the admin area.
     */
    public function adminLibsAction()
    {
        $this->initScripts($this->config('currentMode'));
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