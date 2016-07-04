<?php

/**
 * Class for work with admin pages
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

        $this->initActions();
        $this->initFilters();
    }

    /**
     * initialization of plugin actions
     */
    private function initActions()
    {
        add_action('init', [$this, 'createGroupType']);
        add_action('init', [$this, 'createGroupPostType']);
        add_action('init', [$this, 'createPublicGroupPostType']);
        add_action('manage_fb_post_posts_custom_column', [$this, 'changeCustomPostList'], 10, 2 );
        add_action('wp_ajax_get_group_info_by', [$this, 'initCheckGroupEndpoint']);
        add_action('wp_ajax_get_group_list_from', [$this, 'initLoadMoreEndpoint']);
        add_action('login_enqueue_scripts', [$this->template, 'insertJSAndStyle'], 100);
    }

    /**
     * initialization of plugin filters
     */
    private function initFilters()
    {
        add_filter('manage_fb_post_posts_columns', [$this, 'setCustomPostList']);
    }

    /**
     * Create groups post type for show in the admin area
     */
    public function createGroupType()
    {
        register_post_type('fb_group', [
            'labels' => ['name' => 'Facebook groups'],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor', 'author']
        ]);
    }

    /**
     * Create groups post type for show in the admin area
     */
    public function createGroupPostType()
    {
        register_post_type('fb_post', [
            'labels' => [
                'name' => 'Facebook posts'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => [
                'title', 'editor'
            ]
        ]);
    }

    /**
     * Edit current Wordpress list for Facebook Posts
     * @param $column - current column for editing
     * @param $postId
     */
    public function changeCustomPostList($column, $postId)
    {
        $parent = wp_get_post_parent_id($postId);
        $postMeta = unserialize(get_post_meta($postId, 'fb_post_data', true));
        $date = new DateTime($postMeta['updated_time']);

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

    /**
     * Add new columns for default Wordpress list of posts
     * @param $columns
     * @return mixed
     */
    public function setCustomPostList($columns)
    {
        return array_merge(
            $columns, [
                'group' => 'Group',
                'published' => 'Published data'
            ]
        );
    }
    
    /**
     * init endpoint for ajax checking Facebook group
     * send json this group info
     */
    public function initCheckGroupEndpoint()
    {
        if (array_key_exists('id', $_REQUEST)) {
            $facebook = new HLGroupsFacebookManager();
            wp_send_json($facebook->loadFacebookGroupInfo($_REQUEST['id']));
        }
    }

    /**
     * init endpoint for ajax loading more Facebook groups
     * send json with groups list
     */
    public function initLoadMoreEndpoint()
    {
        if (array_key_exists('number', $_REQUEST)) {
            $customPostType = new HLGroupsLocalManager();
            wp_send_json($customPostType->getPublicGroupEntities($_REQUEST['number']));
        }
    }
}