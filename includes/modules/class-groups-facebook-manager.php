<?php

/**
 * Class for work with facebook groups and posts
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/facebook-manager
 */
class FBGroupsFacebookManager extends FBGroupsEntityManager
{
    /** @var FBGroupsRequest */
    private $request;

    public function __construct()
    {        
        $this->request = new FBGroupsRequest();
    }

    /**
     * Load and save user groups to Wordpress DB as custom post type
     */
    public function loadFacebookGroups()
    {
        $groups = $this->getFacebookGroups();
        $this->saveFacebookGroups($groups);
    }

    /**
     * load all post for user group from Facebook
     * @param int $groupId
     * @param int $postId
     */
    private function loadFacebookPost($groupId, $postId = 0)
    {
        $posts = $this->getFacebookPosts($groupId, $postId);
        
        if (count($posts)) {
            $this->setLastPostUpdate($postId);
            $this->getOldEntities($this->config('userPostsType'), $postId);
            $this->saveFacebookPosts($posts, $postId);
        }
    }

    /**
     * Push message to facebook and create local entity
     * @param int$entity
     * @param string $message
     */
    public function pushFacebookPost($entity, $message)
    {
        $group = get_post_meta($entity, $this->config('userGroupType'), true);
        $this->sendPostToFacebookGroup($group, $message);
    }

    /**
     * Send post to Facebook group
     *
     * @param int $groupId
     * @param string $message
     * @return int|null
     */
    private function sendPostToFacebookGroup($groupId, $message)
    {
        $response = $this->request->makePostRequest($groupId . '/feed', [
            'message' => $message
        ]);

        return array_key_exists('id', $response) ? $response['id'] : null;
    }

    /**
     * 
     * @param int $groupId
     * @param int $postId
     * @param int $offset
     * @return array
     */
    public function getFacebookPosts($groupId, $postId = null, $offset = 0)
    {
        $groupsList = $this->request->makeGetRequest($groupId . '/feed', '', [
            'since'  => get_post_meta($postId, 'last_post_update', true),
            'until'  => 'now',
            'limit'  => 6,
            'offset' => $offset
        ]);

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
    }

    /**
     * Save last post load in the Wordpress storage;
     * @param int $group
     */
    private function setLastPostUpdate($group)
    {
        $date = gmdate('Y-m-d\TH:i:s');
        update_post_meta($group, 'last_post_update', $date);
    }

    /**
     * Get list of facebook groups
     * @return array
     */
    private function getFacebookGroups()
    {
        $groupsList = $this->request->makeGetRequest(
            'me/groups',
            'member_request_count,description,updated_time,name,owner'
        );

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
    }

    /**
     * Save facebook groups to the local storage
     *
     * @param array $groups
     */
    private function saveFacebookGroups(array $groups)
    {
        foreach ($groups as $group) {
            $entity = $this->createLocalEntity(
                $group['name'], 
                $group['description'],
                $this->config('userGroupType'), 
                $group['id']
            );

            $this->updateEntityMeta($entity, $group, $this->config('userGroupType'));
            $this->loadFacebookPost($group['id'], $entity);
        }
    }

    /**
     * Save facebook posts to the local storage
     *
     * @param array $posts
     * @param int $postId
     */
    private function saveFacebookPosts(array $posts, $postId)
    {
        foreach ($posts as $post) {
            $entity = $this->createLocalEntity(
                array_key_exists('story', $post) ? $post['story'] : 'User Post', 
                $post['message'],
                $this->config('userPostsType'), 
                $post['id'],
                $postId
            );
            $this->updateEntityMeta($entity, $post, $this->config('userPostsType'));
        }
    }

    /**
     * Get group info by Facebook Group id
     *
     * @param $groupId
     * @return array
     */
    public function loadFacebookGroupInfo($groupId = 0)
    {
        return $this->request->makeGetRequest(
            $groupId,
            'name,description'
        );
    }

    /**
     * Find Facebook Groups By name
     * @param string $text
     * @param string|null $after
     * @return array
     */
    public function findFacebookGroups($text, $after = null)
    {
        $response = $this->request->makeGetRequest('search', 'id,name,description,privacy', [
            'q'     => urlencode($text),
            'after' => urlencode($after),
            'type'  => 'group'
        ]);

        if (array_key_exists('data', $response)) {
            return [
                'data'   => $response['data'],
                'paging' => $response['paging']
            ];
        }

        return null;
    }
}