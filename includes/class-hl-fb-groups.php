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
    /** @var HLGroupsTemplate  */
    protected $template;
        
    protected function __construct()
    {
        $this->template = HLGroupsTemplate::getInstance();
    }
}