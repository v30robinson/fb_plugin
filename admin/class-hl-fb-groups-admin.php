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
    /**
     * HLGroupsAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        add_action('init', [$this, 'createGroupType']);
        add_action('init', [$this, 'createGroupPostType']);
        add_filter('manage_fb_post_posts_columns', [$this, 'setCustomPostList']);
        add_action('manage_fb_post_posts_custom_column', [$this, 'changeCustomPostList'], 10, 2 );
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
            'labels' => ['name' => 'Facebook posts'],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor']
        ]);
    }

    /**
     * Edit current Wordpress list for Facebook Posts
     * @param $column - currenct c
     * @param $postId
     * 
     * @todo need refactoring!!!
     */
    public function changeCustomPostList($column, $postId)
    {
        $parent = wp_get_post_parent_id($postId);
        $postMeta = unserialize(get_post_meta($postId, 'fb_post_data', true));
        $date = new DateTime($postMeta['updated_time']);

        $fields = [
            'group' => $this->viewHelper->getPostLink($parent, get_the_title($parent)),
            'groupAuthor' => $this->viewHelper->getUserLink(
                get_post_field('post_author', $parent ), get_the_author($parent)
            ),
            'published' => $this->viewHelper->getDate(human_time_diff($date->getTimestamp(), time()), 'test')
        ];

        if (array_key_exists($column, $fields)) {
            echo $fields[$column];
        }
    }

    /**
     * Add new colums for default Wordpress list of posts
     * @param $columns
     * @return mixed
     */
    public function setCustomPostList($columns)
    {
        $columns['group']  = 'Group';
        $columns['groupAuthor'] = 'Group Author';
        $columns['published'] = 'Published data';
        
        return $columns;
    }
}