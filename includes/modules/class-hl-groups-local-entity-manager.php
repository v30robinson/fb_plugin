<?php

/**
 * Class for work with local entities
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-groups
 * @subpackage     hl-groups/local-entity-manager
 */
class HLGroupsLocalEntityManager
{
    /**
     * Create new entity or update if exists
     * @param string $name
     * @param string $description
     * @param int $entityId
     * @param string $entityType
     * @param int $entityParent
     * @return int|WP_Error
     */
    protected function createLocalEntity($name, $description, $entityType, $entityId = null, $entityParent = 0)
    {
        $localEntity = $this->getLocalEntityId($entityId, $entityType);

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
    protected function updateLocalEntity($entityId, $name, $description)
    {
        return wp_update_post([
            'ID'           => $entityId,
            'post_title'   => $name,
            'post_content' => $description
        ]);
    }

    /**
     * Update entity meta data
     * @param $postId
     * @param $entityData
     * @param $entityType
     */
    protected function updateEntityMeta($postId, $entityData, $entityType)
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
    protected function getLocalEntityId($entity, $type)
    {
        $posts = get_posts([
            'meta_key'       => $type,
            'meta_value'     => $entity,
            'post_type'      => $type,
            'posts_per_page' => 1
        ]);

        return count($posts) > 0 ? $posts[0]->ID : null;
    }

    /**
     * Get all groups entities by user id
     * @param int $userId
     * @return array
     */
    public function getGroupEntities($userId)
    {
        $groups = [];
        $customPosts = get_posts([
            'post_type' => 'fb_group',
            'author'    => $userId
        ]);

        foreach($customPosts as $post) {
            array_push($groups, [
                'title'       => $post->post_title,
                'description' => $post->post_content,
                'posts'       => $this->getPostEntities($post->ID)
            ]);
        }

        return $groups;
    }

    /**
     * Get all posts entities by group id
     * @param int $groupId
     * @return array
     */
    protected function getPostEntities($groupId)
    {
        $posts = [];
        $customPosts = get_posts([
            'post_type'   => 'fb_post',
            'post_parent' => $groupId
        ]);

        foreach($customPosts as $post) {
            array_push($posts, [
                'title'       => $post->post_title,
                'description' => $post->post_content,
            ]);
        }

        return $posts;
    }
}