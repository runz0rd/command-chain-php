<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 00:23
 */

namespace Command;

class CommandChain {

    /**
     * @var ICommand[]
     */
    private $commands;

    /**
     * @var ICommand[]
     */
    private $completedCommands;

    /**
     * @var \Exception[]
     */
    private $exceptionStack = array();

    /**
     * @param $name
     * @param $instance
     * @param $method
     * @param array|\Closure[] $arguments
     * @throws \Exception
     * @return ICommand
     */
    public function add($name, $instance, $method, array $arguments = array()) {
        if(isset($this->commands[$name])) {
            throw new \Exception('Command under name ' . $name . ' already registered.');
        }
        $command = new Command($name, $instance, $method, $arguments);
        $this->commands[] = $command;
        return $command;
    }

    /**
     * @param boolean $failSilently
     */
    public function run($failSilently = false) {
        foreach ($this->commands as $command) {
            $this->runCommand($command, $failSilently);
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getResult($name) {
        return $this->findCompletedCommand($name)->getResult();
    }

    public function getExceptionStack() {
        return $this->exceptionStack;
    }

    /**
     * @return ICommand[]
     */
    public function getCompletedCommands() {
        return $this->completedCommands;
    }

    /**
     * @return ICommand
     */
    public function getCompletedCommand($name) {
        return $this->findCompletedCommand($name);
    }

    /**
     * @param $name
     * @return ICommand
     * @throws \Exception
     */
    private function findCompletedCommand($name) {
        foreach($this->completedCommands as $completedCommand) {
            if($completedCommand->getName() == $name) {
                $command = $completedCommand;
            }
        }
        if(!isset($command)) {
            throw new \Exception('No completed command with name ' . $name . ' found.');
        }
        return $command;
    }

    private function processArguments(array $arguments) {
        for($i=0; $i<count($arguments); $i++) {
            if($arguments[$i] instanceof \Closure) {
                $arguments[$i] = $arguments[$i]($this);
            }
        }
        return $arguments;
    }

    private function runCommand(ICommand $command, $failSilently) {
        try {
            $arguments = $this->processArguments($command->getArguments());
            $command->run($arguments);
            $this->completedCommands[] = $command;
        }
        catch(\Exception $ex) {
            $this->exceptionStack[] = $ex;
            $this->rollback();
            if(!$failSilently) {
                throw $ex;
            }
        }
    }

    private function rollback() {
        $completedCommands = $this->completedCommands;
        for($i=count($completedCommands)-1; $i>=0; $i--) {
            try {
                $completedCommand = $completedCommands[$i];
                if($completedCommand->hasRollback()) {
                    $rollback = $completedCommand->getRollback();
                    $arguments = $this->processArguments($rollback->getArguments());
                    $rollback->run($arguments);
                    $this->completedCommands[] = $rollback;
                }
            }
            catch(\Exception $ex) {
                $this->exceptionStack[] = $ex;
            }
        }
    }
}