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
class HLGroupsEntityManager
{
    /** @var stdClass of config options */
    protected $config;

    public function __construct()
    {
        $this->config = new stdClass();
        $this->config->userGroupType   = 'fb_group';
        $this->config->userPostsType   = 'fb_post';
        $this->config->publicGroupType = 'fb_group_public';
    }

    /**
     * Create new entity or update if exists
     * 
     * @param string $name
     * @param string $description
     * @param int $entityId
     * @param string $entityType
     * @param int $entityParent
     * @return int|WP_Error
     */
    public function createLocalEntity($name, $description, $entityType, $entityId = 0, $entityParent = 0)
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
     *
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
     * Get entity meta data
     * @param int $postId
     * @param string $entityType
     * @return array
     */
    protected function getLocalEntityMeta($postId, $entityType)
    {
        $entityData = get_post_meta($postId, $entityType . '_data', true);
        return unserialize($entityData);
    }

    /**
     * Update entity meta data
     *
     * @param int $postId
     * @param array $entityData
     * @param string $entityType
     */
    public function updateEntityMeta($postId, $entityData, $entityType)
    {
        if (is_array($entityData) 
            && array_key_exists('id', $entityData)
        ) {
            update_post_meta($postId, $entityType, $entityData['id']);
            update_post_meta($postId, $entityType . '_data', serialize($entityData));
        }
    }

    /**
     * Try to get local group or post id by entity id and type
     *
     * @param int $entity - custom post id
     * @param string $type - custom post type
     * @return int|null
     */
    protected function getLocalEntityId($entity, $type)
    {
        $posts = get_posts([
            'meta_key'       => $type,
            'meta_value'     => $entity,
            'post_type'      => $type,
            'posts_per_page' => 1000
        ]);

        return count($posts) > 0 ? $posts[0]->ID : null;
    }
}