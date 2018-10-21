<?php
/**
 * User: joachimdoerr
 * Date: 21.10.18
 * Time: 20:01
 */

namespace Basecondition\Provider;


use Basecondition\Utils\MBlockHelper;
use rex_request;
use rex_response;

class MBlockOutputProvider
{
    /**
     * @param string $key
     * @author Joachim Doerr
     */
    public static function provideMBlockOutput($key = 'add_mblock_block')
    {
        // mblock form part for ajax call
        if (rex_request::get($key, 'int', 0)) {
            // open output buffer
            rex_response::cleanOutputBuffers();
            // print form part
            print MBlockHelper::getAddMBlock();
            exit();
        }
    }
}