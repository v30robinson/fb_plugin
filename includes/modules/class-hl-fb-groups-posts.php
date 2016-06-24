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
    /** @var HLGroupsRequest */
    private $request;

    /**
     * HLGroupsCustomPosts constructor
     * @param $token - user token for work with Facebook
     */
    public function __construct($token)
    {
        $this->request = new HLGroupsRequest();
        $this->token = $token;
    }

    /**
     * Load and save user groups to Wordpress DB as custom post type
     */
    public function loadFacebookGroups()
    {
        $groups = $this->getFacebookGroups($this->token);
        
        foreach ($groups as $group) {
            $entity = $this->createLocalEntity($group['name'], $group['description'], 'fb_group', $group['id']);
            $this->updateEntityMeta($entity, $group, 'fb_group');
            $this->loadPostByGroupId($group['id'], $entity);
        }
    }

    /**
     * load all post for user group
     * @param $groupId
     * @param $postId
     */
    private function loadPostByGroupId($groupId, $postId = 0)
    {
        $posts = $this->getFacebookPosts($groupId, $this->token);

        foreach ($posts as $post) {
            $title  = array_key_exists('story', $post) ? $post['story'] : 'User Post';
            $entity = $this->createLocalEntity($title, $post['message'], 'fb_post', $post['id'], $postId);
            $this->updateEntityMeta($entity, $post, 'fb_post');
        }
    }

    private function getFacebookPosts($groupId, $token)
    {
        $groupsList = $this->request->makeRequest($token, $groupId . '/feed');

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
    }

    /**
     * Get list of facebook groups
     * @return array
     */
    private function getFacebookGroups($token)
    {
        $groupsList = $this->request->makeRequest(
            $token,
            'me/groups',
            'member_request_count,description,updated_time,name,owner'
        );

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
    }

    /**
     * Create new entity or update if exists
     * @param string $name
     * @param string $description
     * @param int $entityId
     * @param string $entityType
     * @param int $entityParent
     * @return int|WP_Error
     */
    private function createLocalEntity($name, $description, $entityType, $entityId = null, $entityParent = 0)
    {
        $localEntity = $this->getLocalEntity($entityId, $entityType);

        if (!$localEntity) {
            return wp_insert_post([
                'post_title'   => $name,
                'post_content' => $description,
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
                'post_type'    => $entityType,
                'post_parent'  => $entityParent
            ]);
        }

        return $this->updateLocalEntity($localEntity, $name, $description);
    }

    /**
     * Update existing Wordpress custom post
     * @param int $entityId
     * @param string $name
     * @param string $description
     * @return int|WP_Error
     */
    private function updateLocalEntity($entityId, $name, $description)
    {
        return wp_update_post([
            'ID' => $entityId,
            'post_title' => $name,
            'post_content' => $description
        ]);
    }

    /**
     * Update entity meta data
     * @param $postId
     * @param $entityData
     * @param $entityType
     */
    private function updateEntityMeta($postId, $entityData, $entityType)
    {
        if (is_array($entityData) && array_key_exists('id', $entityData)) {
            update_post_meta($postId, $entityType, $entityData['id']);
            update_post_meta($postId, $entityType . '_data', serialize($entityData));
        }
    }

    /**
     * Try to get local group or post id by entity id and type
     * @param $entity - custom post id3
     * @param $type - custom post type
     * @return int|null
     */
    private function getLocalEntity($entity, $type)
    {
        $posts = get_posts([
            'meta_key' => $type,
            'meta_value' => $entity,
            'post_type' => $type,
            'posts_per_page' => 1
        ]);

        return count($posts) > 0 ? $posts[0]->ID : null;
    }
}