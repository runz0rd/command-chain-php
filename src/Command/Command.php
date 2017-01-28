<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 00:21
 */

namespace Command;

class Command implements ICommand {

    /**
     * @var object
     */
    public $instance;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $arguments;

    /**
     * @var Command
     */
    public $rollback;

    /**
     * @var mixed
     */
    public $result;

    /**
     * Command constructor.
     * @param object $instance
     * @param string $method
     * @param array $arguments
     * @throws \Exception
     */
    public function __construct($instance, $method, array $arguments = array()) {
        if(!method_exists($instance, $method)) {
            throw new \Exception('Method ' . $method . ' not found in class ' . get_class($instance));
        }
        $this->instance = $instance;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function addRollback($instance, $method, array $arguments = array()) {
        $this->rollback = new Command($instance, $method, $arguments);
    }

    public function run($arguments = array()) {
        if(!empty($arguments)) {
            $this->arguments = $arguments;
        }
        $this->result = call_user_func_array(array($this->instance, $this->method), $this->arguments);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Command
     */
    public function getRollback()
    {
        return $this->rollback;
    }

    /**
     * @return array
     */
    function getArguments()
    {
        return $this->arguments;
    }
}