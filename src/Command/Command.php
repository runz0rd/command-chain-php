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
     * @var string
     */
    protected $name;

    /**
     * @var object
     */
    protected $instance;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var ICommand
     */
    protected $rollback;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * Command constructor.
     * @param string $name
     * @param object $instance
     * @param string $method
     * @param array $arguments
     * @throws \Exception
     */
    public function __construct($name, $instance, $method, array $arguments = array()) {
        if(!method_exists($instance, $method)) {
            throw new \Exception('Method ' . $method . ' not found in class ' . get_class($instance));
        }
        $this->name = $name;
        $this->instance = $instance;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function addRollback($instance, $method, array $arguments = array()) {
        $this->rollback = new Command($this->name . '-rollback', $instance, $method, $arguments);
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
     * @return bool
     */
    public function hasRollback() {
        $result = false;
        if(isset($this->rollback)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return ICommand
     */
    public function getRollback()
    {
        return $this->rollback;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}