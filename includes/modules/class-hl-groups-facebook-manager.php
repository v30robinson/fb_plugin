<?php

/**
 * Class for work with facebook groups and posts
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-groups
 * @subpackage     hl-groups/facebook-manager
 */
class HLGroupsFacebookManager extends HLGroupsEntityManager
{
    /** @var HLGroupsRequest */
    private $request;

    public function __construct()
    {        
        $this->request = new HLGroupsRequest();
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
        $posts = $this->getFacebookPosts($groupId);
        $this->saveFacebookPosts($posts, $postId);
    }

    /**
     * Push message to facebook and create local entity
     * @param int$entity
     * @param string $message
     */
    public function pushFacebookPost($entity, $message)
    {
        $group = get_post_meta($entity, $this->config('userGroupType'), true);
        $post = $this->sendPostToFacebookGroup($group, $message);

        if ($post) {
            $postId = $group . '_' . $post;
            $this->createLocalEntity('User Post', $message, $this->config('userPostsType'), $postId, $entity);
        }
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
     * @return array
     */
    private function getFacebookPosts($groupId)
    {
        $groupsList = $this->request->makeGetRequest($groupId . '/feed');

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
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