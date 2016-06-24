<?php

/**
 * Class-helper for work with HTML entities
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/modules/view-helper
 */
class HLGroupsViewHelper
{
    /**
     * Create HTML node as link
     * @param string $url
     * @param string $name
     * @return string HTML
     */
    private function createLink($url, $name)
    {
        $dom = new DOMDocument();
        $element = $dom->createElement('a', $name);
        $element->setAttribute('attr', $url);
        $dom->appendChild($element);

        return $dom->saveHTML();
    }

    /**
     * Get user link as HTML tag
     * @param $userId
     * @param $userName
     * @return string
     */
    public function getUserLink($userId, $userName)
    {
        return $this->createLink(
            '/wp-admin/user-edit.php?user_id=' . $userId,
            $userName
        );
    }

    /**
     * Get post link as HTML tag
     * @param $postId
     * @param $postName
     * @return string
     */
    public function getPostLink($postId, $postName)
    {
        return $this->createLink(
            '/wp-admin/post.php?post=' . $postId .'&action=edit',
            $postName
        );
    }

    /**
     * Get date format
     * @param $date
     * @param $title
     * @return string
     */
    public function getDate($date, $title)
    {
        $dom = new DOMDocument();
        $element = $dom->createElement('abbr', $date);
        $element->setAttribute('title', $title);
        $dom->appendChild($element);

        return $dom->saveHTML();
    }
}