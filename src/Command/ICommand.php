<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 00:30
 */

namespace Command;


interface ICommand {

    /**
     * @param object $instance
     * @param string $method
     * @param array $arguments
     */
    function addRollback($instance, $method, array $arguments = array());

    /**
     * @throws \Exception
     * @param array $arguments;
     * @return mixed
     */
    function run($arguments = array());

    /**
     * @return mixed
     */
    function getResult();

    /**
     * @return bool
     */
    function hasRollback();

    /**
     * @return Command
     */
    function getRollback();

    /**
     * @return array
     */
    function getArguments();
}