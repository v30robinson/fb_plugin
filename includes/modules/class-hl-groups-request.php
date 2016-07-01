<?php

/**
 * Class for work with request
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/request
 */
class HLGroupsRequest
{
    /**
     * Create url for request
     * @param string $userToken
     * @param string $endpoint
     * @param string $fields
     * @param array $attr
     * @return string
     */
    private function createUrl($userToken, $endpoint, $fields = '', $attr = [])
    {
        $url  = 'https://graph.facebook.com/v2.6/' . $endpoint;
        $attr = array_merge($attr, [
            'access_token' => $userToken,
            'fields'       => $fields
        ]);
        
        return add_query_arg($attr, $url);
    }

    /**
     * Make POST request to Facebook API using endpoint
     * @param string $userToken
     * @param string $endpoint
     * @param array $attr
     * @return array
     */
    public function makePostRequest($userToken, $endpoint, $attr)
    {
        $requestBody = [];

        if ($userToken) {
            $request     = wp_remote_post($this->createUrl($userToken, $endpoint, '', $attr));
            $requestBody = json_decode(wp_remote_retrieve_body($request), true);
        }

        return $requestBody;
    }
    
    /**
     * Make GET request to Facebook API using endpoint
     * @param string $userToken
     * @param string $endpoint
     * @param string $fields
     * @return array
     */
    public function makeGetRequest($userToken, $endpoint, $fields = '')
    {
        $requestBody = [];

        if ($userToken) {
            $request     = wp_remote_get($this->createUrl($userToken, $endpoint, $fields));
            $requestBody = json_decode(wp_remote_retrieve_body($request), true);
        }

        return $requestBody;
    }
}