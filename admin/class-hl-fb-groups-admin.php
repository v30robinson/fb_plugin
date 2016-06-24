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

        $this->initAdminActions();
    }

    /**
     * Init actions for work with facebook users.
     */
    private function initAdminActions()
    {
        add_action('init', [$this, 'createGroupPostType']);
    }

    /**
     * Create groups post type for show in the admin area
     */
    public function createGroupPostType()
    {
        register_post_type('fb_group', [
            'labels' => [
                'name' => 'Facebook groups',
                'singular_name' => 'Facebook groups'
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => [
                'title', 'editor', 'author'
            ]
        ]);
    }
}