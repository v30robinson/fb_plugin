<?php

/**
 * Class for work with local groups and posts
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-groups
 * @subpackage     hl-groups/local-manager
 */
class HLGroupsLocalManager extends HLGroupsEntityManager
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
            'post_type'   => $this->config->userGroupType,
            'author'      => $userId,
            'numberposts' => 1000,
            'orderby'     => 'date'
        ]);

        foreach ($customPosts as $post) {
            array_push($groups, [
                'id'          => $post->ID,
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
            'post_type'   => $this->config->userPostsType,
            'post_parent' => $groupId,
            'numberposts' => 1000,
            'orderby'     => 'date'
        ]);

        foreach ($customPosts as $post) {
            array_push($posts, [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'description' => $post->post_content,
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
            $this->config->publicGroupType
        );

        $this->updateEntityMeta($group, $data, $this->config->publicGroupType);
    }

    /**
     * Get all posts entities by group id
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function getPublicGroupEntities($offset = 0, $count = 5)
    {
        $posts = [];
        $customPosts = get_posts([
            'post_type'      => $this->config->publicGroupType,
            'posts_per_page' => $count,
            'offset'         => $offset,
            'orderby'        => 'date'
        ]);

        foreach ($customPosts as $post) {
            $posts[] = $this->getLocalEntityMeta($post->ID, $this->config->publicGroupType);
        }

        return $posts;
    }

    /**
     * Return count of publish public groups
     * @return int
     */
    public function countOfPublicGroupPages()
    {
        return wp_count_posts($this->config->publicGroupType)->publish;
    }
}