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
    /** @var string */
    private $token;

    public function __construct()
    {
        $this->token = $this->updateUserToken();
    }

    /**
     * update and get user token from request or from local storage
     * @return string
     */
    private function updateUserToken()
    {
        $token = null;

        if ($_POST['fb_response']['authResponse']['accessToken']) {
            $token = $_POST['fb_response']['authResponse']['accessToken'];
            update_user_meta(get_current_user_id(), 'fb-token', $token);
        } else {
            $token = get_user_meta(get_current_user_id(), 'fb-token', true);
        }

        return $token;
    }

    /**
     * Create url for request
     * @param string $endpoint
     * @param string $fields
     * @param array $attr
     * @return string
     */
    private function createUrl($endpoint, $fields = '', $attr = [])
    {
        $url  = 'https://graph.facebook.com/v2.6/' . $endpoint;
        $attr = array_merge($attr, [
            'access_token' => $this->token,
            'fields'       => $fields
        ]);
        
        return add_query_arg($attr, $url);
    }

    /**
     * Make POST request to Facebook API using endpoint
     * @param string $endpoint
     * @param array $attr
     * @return array
     */
    public function makePostRequest($endpoint, $attr)
    {
        $requestBody = [];

        if ($this->token) {
            $request     = wp_remote_post($this->createUrl($endpoint, '', $attr));
            $requestBody = json_decode(wp_remote_retrieve_body($request), true);
        }

        return $requestBody;
    }
    
    /**
     * Make GET request to Facebook API using endpoint
     * @param string $endpoint
     * @param string $fields
     * @return array
     */
    public function makeGetRequest($endpoint, $fields = '')
    {
        if ($this->token) {
            $request  = wp_remote_get($this->createUrl($endpoint, $fields));
            return $this->parseRequest($request);
        }
        
        return [];        
    }


    /**
     * Try to parse response from Facebook
     * @param array $response
     * @return array
     * @throws Exception
     */
    private function parseRequest(array $response)
    {
        $responseBody = json_decode(wp_remote_retrieve_body($response), true);

        if (array_key_exists('error', $responseBody)) {
            $responseBody = [
                'error' => $responseBody['error']['message']
            ];
        }
        return $responseBody;
    }
}