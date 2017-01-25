<?php

/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 01:14
 */
class MockCommand
{
    /**
     * @param mixed $arg
     * @param bool $throwEx
     * @return mixed
     * @throws Exception
     */
    public function testMethod($arg, $throwEx = false) {
        if($throwEx) {
            throw new Exception('bad!');
        }
        return $arg;
    }
}