<?php

/**
 * Class for work with custom post types
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/custom-posts
 */
class HLGroupsCustomPosts
{
    /**
     * Get list of facebook groups
     * @return array
     */
    private function getFacebookGroups($request)
    {
        return array_key_exists('data', $request)
            ? $request['data']
            : [];
    }
    
    /**
     * Save user groups and posts to Wordpress DB as custom post type
     */
    public function saveFacebookGroups($request)
    {
        $groups = $this->getFacebookGroups($request);
        foreach ($groups as $group) {
            $postId = $this->createLocalGroup($group['id'], $group['name'], $group['description']);
            $this->updateGroupMeta($postId, $group);
        }
    }
    
    /**
     * Create new group or update if exist
     * @param $id
     * @param $name
     * @param $description
     * @return int|WP_Error
     */
    private function createLocalGroup($id, $name, $description)
    {
        $localGroup = $this->getLocalGroupById($id);

        if (!$localGroup) {
            return wp_insert_post([
                'post_title' => $name,
                'post_content' => $description,
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'fb_group'
            ]);
        }

        return $this->updateLocalGroup($localGroup, $name, $description);
    }

    /**
     * update existing user group
     * @param $id - local group id
     * @param $name
     * @param $description
     * @return int|WP_Error
     */
    private function updateLocalGroup($id, $name, $description)
    {
        return wp_update_post([
            'ID' => $id,
            'post_title' => $name,
            'post_content' => $description
        ]);
    }

    /**
     * Update group meta data
     * @param $postId
     * @param $groupData
     */
    private function updateGroupMeta($postId, $groupData)
    {
        if (is_array($groupData) && array_key_exists('id', $groupData)) {
            update_post_meta($postId, 'fb_group', $groupData['id']);
            update_post_meta($postId, 'fb_group_data', serialize($groupData));
        }
    }

    /**
     * Try to get local group id
     * @param $groupId - facebook group id
     * @return int|null
     */
    private function getLocalGroupById($groupId)
    {
        $posts = get_posts([
            'meta_key' => 'fb_group',
            'meta_value' => $groupId,
            'post_type' => 'fb_group',
            'posts_per_page' => 1
        ]);

        return count($posts) > 0 ? $posts[0]->ID : null;
    }
}