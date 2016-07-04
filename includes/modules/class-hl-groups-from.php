<?php

/**
 * Class for work with form data
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/forms
 */
class HLGroupsForm
{
    /**
     * Parse user post form and publish post in the FB group
     * @param array $data
     */
    public function parseUserPostFrom($data)
    {
        if ($this->validateUserPost($data)) {
            $facebookManager = new HLGroupsFacebookManager();
            $facebookManager->pushFacebookPost(
                $data['fb-group-id'],
                $data['fb-group-post']
            );
        }
    }

    /**
     * Parse public group form and publish post in the FB group
     * @param $data
     */
    public function parsePublicGroupForm($data)
    {
        if ($this->validatePublicGroup($data)) {
            $entityManager = new HLGroupsLocalManager();
            $entityManager->savePublicGroupEntity(
                $this->createGroupFormData($data)
            );
        }
    }

    /**
     * Create group data for saving in the local storage
     * @param array $data
     * @return array
     */
    private function createGroupFormData(array $data)
    {
        return [
            'name'        => sanitize_text_field($data['fb-group-name']),
            'description' => sanitize_text_field($data['fb-group-description']),
            'url'         => sanitize_text_field($data['fb-group-url']),
            'members'     => sanitize_text_field($data['fb-group-members']),
            'id'          => sanitize_text_field($this->parseUrl($data['fb-group-url']))
        ];
    }

    /**
     * Get group id from url
     * @param string $url
     * @return int|null
     */
    private function parseUrl($url)
    {
        if (preg_match('~https://www.facebook.com/groups/(.+?)/~is', $url, $match )) {
            return $match[1];
        }
        return null;
    }

    /**
     * Validator for user post form
     * @param array $data
     * @return bool
     */
    private function validateUserPost($data)
    {
        if (array_key_exists('fb-group-id', $data)
            && array_key_exists('fb-group-post', $data)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Validator for public group form
     * @param array $data
     * @return bool
     */
    private function validatePublicGroup($data)
    {
        if (array_key_exists('fb-group-url', $data)
            && array_key_exists('fb-group-name', $data)
            && array_key_exists('fb-group-description', $data)
            && array_key_exists('fb-group-members', $data)
            && $this->parseUrl($data['fb-group-url'])
        ) {
            return true;
        }
        return false;
    }
}