<?php

/**
 * Class for work with request
 *
 * @link           http://nicktemple.com/
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        fb-groups
 * @subpackage     fb-groups/request
 */
class FBGroupsRequest extends FBGroupsConfig
{
    /** @var string */
    private $token;

    /**
     * Make POST request to Facebook API using endpoint
     * @param string $endpoint
     * @param array $attr
     * @return array
     */
    public function makePostRequest($endpoint, $attr = [])
    {
        $response = wp_remote_post($this->createUrl($endpoint), $this->createPostData($attr));
        return $this->parseRequest($response);
    }

    /**
     * Make GET request to Facebook API using endpoint
     * @param string $endpoint
     * @param string $fields
     * @param array $attr
     * @return array
     */
    public function makeGetRequest($endpoint, $fields = '', $attr = [])
    {
        $request  = wp_remote_get($this->createUrl($endpoint, $fields, $attr));
        return $this->parseRequest($request);
    }

    /**
     * get local user token
     * @return string
     */
    private function getUserToken()
    {
        if (!$this->token) {
            $this->token = $this->updateUserToken();
        }
        return $this->token;
    }

    /**
     * Update local token for work with Facebook API
     * @return mixed
     */
    private function updateUserToken()
    {
        if (isset($_POST['fb_response']['authResponse']['accessToken'])) {
            update_user_meta(
                get_current_user_id(),
                'fb-token',
                $_POST['fb_response']['authResponse']['accessToken']
            );
        }
        return get_user_meta(get_current_user_id(), 'fb-token', true);
    }

    /**
     * Create array for post data
     * @param array $postData
     * @return array
     */
    private function createPostData($postData)
    {
        return [
            'body' => $postData
        ];
    }

    /**
     * If token has been expired - logout user from website
     * @param $error
     * @return bool
     */
    private function isTokenError($error)
    {
        return array_key_exists('type', $error) && $error['type'] == 'OAuthException';
    }

    /**
     * Create array with information about error
     * @param array $error
     * @return array
     */
    private function createResponseError(array $error = [])
    {
        if ($this->isTokenError($error)) {
            wp_logout();
        }

        if (array_key_exists('code', $error) && array_key_exists('message', $error)) {
            return [
                'code'    => $error['code'],
                'message' => $error['message']
            ];
        }
        return [];
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
        $requestUrl = array_merge($attr, [
            'access_token' => $this->getUserToken(),
            'fields'       => $fields
        ]);
        
        return add_query_arg(
            $requestUrl,
            $this->config('facebookApi') . $endpoint
        );
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

        return array_key_exists('error', $responseBody)
            ? $this->createResponseError($responseBody['error'])
            : $responseBody;
    }
}