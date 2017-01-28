<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 01:14
 */
class MockCommand
{

    private $throwEx;

    public function __construct($throwEx = false) {
        $this->throwEx = $throwEx;
    }

    /**
     * @param mixed $arg
     * @return mixed
     * @throws Exception
     */
    public function testMethod($arg) {
        if($this->throwEx) {
            throw new Exception($arg);
        }
        return $arg;
    }
}