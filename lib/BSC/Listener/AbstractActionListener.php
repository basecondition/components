<?php
/**
 * User: joachimdorr
 * Date: 16.04.20
 * Time: 23:12
 */

namespace BSC\Listener;


use BSC\Trait\Authorization;
use BSC\Trait\Provider;

abstract class AbstractActionListener
{
    use Authorization;
    use Provider;
}