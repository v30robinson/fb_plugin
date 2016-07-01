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
class HLGroupsFacebookManager extends HLGroupsLocalEntityManager
{
    /** @var HLGroupsRequest */
    private $request;
    
    /** @var string  */
    private $token;

    /**
     * HLGroupsCustomPosts constructor
     * @param $token - user token for work with Facebook
     */
    public function __construct($token = null)
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
            $this->loadFacebookPost($group['id'], $entity);
        }
    }

    /**
     * load all post for user group from Facebook
     * @param $groupId
     * @param $postId
     */
    private function loadFacebookPost($groupId, $postId = 0)
    {
        $posts = $this->getFacebookPosts($groupId, $this->token);

        foreach ($posts as $post) {
            $title  = array_key_exists('story', $post) ? $post['story'] : 'User Post';
            $entity = $this->createLocalEntity($title, $post['message'], 'fb_post', $post['id'], $postId);
            $this->updateEntityMeta($entity, $post, 'fb_post');
        }
    }

    /**
     * Push message to facebook and create local entity
     * @param $entity
     * @param $message
     */
    public function pushFacebookPost($entity, $message)
    {
        $group = get_post_meta($entity, 'fb_group', true);
        $post = $this->sendPostToFacebookGroup($group, $message);

        if ($post) {
            $this->createLocalEntity('User Post', $message, 'fb_post', $post, $entity);
        }
        
    }

    /**
     * Send post to Facebook group
     * @param int $groupId
     * @param string $message
     * @return int|null
     */
    private function sendPostToFacebookGroup($groupId, $message)
    {
        $request  = new HLGroupsRequest();
        $response = $request->makePostRequest($this->token, $groupId . '/feed', [
            'message' => $message
        ]);

        return array_key_exists('id', $response) ? $response['id'] : null;
    }

    /**
     * @param $groupId
     * @param $token
     * @return array
     */
    private function getFacebookPosts($groupId, $token)
    {
        $groupsList = $this->request->makeGetRequest($token, $groupId . '/feed');

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
        $groupsList = $this->request->makeGetRequest(
            $token,
            'me/groups',
            'member_request_count,description,updated_time,name,owner'
        );

        return array_key_exists('data', $groupsList)
            ? $groupsList['data']
            : [];
    }
}