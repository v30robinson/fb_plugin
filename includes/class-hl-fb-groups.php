<?php

/**
 * Core plugin class
 *
 * @link           http://www.healerslibrary.com
 * @license        http://www.mev.com/license.txt
 * @copyright      2016 by MEV, LLC
 * @since          1.0
 * @author         Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author         Nick Temple <nick@intellispire.com>
 * @package        hl-fb-groups
 * @subpackage     hl-fb-groups/core
 */
class HLGroupsCore
{
    /**
     * @var HLGroupsCustomPosts
     */
    protected $customPostType;
    
    /**
     * HLGroupsCore constructor.
     */
    public function __construct()
    {
        $this->customPostType = new HLGroupsCustomPosts();
        $this->request = new HLGroupsRequest();
    }
}