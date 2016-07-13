<?php

/**
 * Class for work with local groups and posts
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/local-manager
 */
class FBGroupsLocalManager extends FBGroupsEntityManager
{
    /**
     * Get all groups entities by user id
     * @param int $userId
     * @return array
     */
    public function getGroupEntities($userId)
    {
        $groups = [];
        $customPosts = get_posts([
            'post_type'   => $this->config('userGroupType'),
            'author'      => $userId,
            'numberposts' => 1000,
            'orderby'     => 'date'
        ]);

        foreach ($customPosts as $post) {
            array_push($groups, [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'description' => $post->post_content,
                'posts'       => $this->getPostEntities($post->ID),
                'data'        => $this->getLocalEntityMeta($post->ID, $this->config('userGroupType'))
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
            'post_type'   => $this->config('userPostsType'),
            'post_parent' => $groupId,
            'numberposts' => 1000,
            'orderby'     => 'date'
        ]);

        foreach ($customPosts as $post) {
            array_push($posts, [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'description' => $post->post_content
            ]);
        }

        return $posts;
    }

    /**
     * Save public group to DB
     * @param $data
     */
    public function savePublicGroupEntity($data)
    {
        $group = $this->createLocalEntity(
            $data['name'],
            $data['description'],
            $this->config('publicGroupType')
        );

        $this->updateEntityMeta($group, $data, $this->config('publicGroupType'));
    }

    /**
     * Get all posts entities by group id
     * @param int $offset
     * @param int $count
     * @param string $findBy
     * @return array
     */
    public function getPublicGroupEntities($offset = 0, $count = 5, $findBy = '')
    {
        $posts = [];
        $customPosts = get_posts([
            'post_type'      => $this->config('publicGroupType'),
            'posts_per_page' => $count,
            'offset'         => $offset,
            'orderby'        => 'date',
            's'              => $findBy
        ]);

        foreach ($customPosts as $post) {
            $posts[] = $this->getLocalEntityMeta($post->ID, $this->config('publicGroupType'));
        }

        return $posts;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function findLocalGroupsByIds($ids = [])
    {
        $entities = $this->findEntitiesByIds($ids);
        $result   = [];

        foreach ($entities as $entity) {
            $result[] = get_post_meta($entity->ID, $this->config('publicGroupType'), true);
        }

        return $result;
    }

    /**
     * Merge local groups with facebook public groups
     * @param array $facebookGroups
     * @param array $localEntities
     * @return array
     */
    public function mergeLocalGroup($facebookGroups, $localEntities)
    {
        if (array_key_exists('data', $facebookGroups)) {
            foreach ($facebookGroups['data'] as &$group) {
                $group['localExist'] = in_array($group['id'], $localEntities);
            }
        }

        return $facebookGroups;
    }
    
    /**
     * Return count of publish public groups
     * @return int
     */
    public function countOfPublicGroupPages()
    {
        return wp_count_posts($this->config('publicGroupType'))->publish;
    }

    /**
     * Delete local Entity
     * @param $entity
     */
    public function deleteLocalEntityById($entity)
    {
        if (get_post_status($entity) != false) {
            wp_delete_post($entity, true);
        }
    }
}