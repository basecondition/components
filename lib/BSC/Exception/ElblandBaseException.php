<?php

namespace BSC\Exception;


use Throwable;

class BaseException extends \Exception
{
    /**
     * @var string
     */
    private $key;

    public function __construct($message = "", $key = "", $code = 0, Throwable $previous = null) {
        $this->key = $key;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getKey()
    {
        return $this->key;
    }
}