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
        
        $this->initActions($this->plugin->mode);
        $this->initFilters($this->plugin->mode);
    }

    /**
     * initialization custom post types
     */
    public function initPostTypesAction()
    {
        foreach ($this->getConfig('postType') as $key => $postType) {
            register_post_type($key, [
                'labels'      => $postType->labels,
                'public'      => $postType->public,
                'has_archive' => $postType->has_archive,
                'supports'    => $postType->supports
            ]);
        }
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

    /**
     * Add new columns for default Wordpress list of posts.
     * @param $columns
     * @return mixed
     */
    public function customPostListFilter($columns)
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
            $facebook = new HLGroupsFacebookManager();
            wp_send_json($facebook->loadFacebookGroupInfo($_REQUEST['id']));
        }
    }

    /**
     * Init endpoint for ajax loading more Facebook groups;
     * send json with groups list.
     */
    public function loadMoreEndpointAction()
    {
        if (array_key_exists('number', $_REQUEST)) {
            $customPostType = new HLGroupsLocalManager();
            wp_send_json($customPostType->getPublicGroupEntities($_REQUEST['number']));
        }
    }
}