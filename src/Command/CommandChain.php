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
     * @param array $arguments
     * @throws \Exception
     * @return ICommand
     */
    public function add($name, $instance, $method, array $arguments = array()) {
        if(isset($this->commands[$name])) {
            throw new \Exception('Command under name ' . $name . ' already registered.');
        }
        $this->commands[$name] = new Command($instance, $method, $arguments);
        return $this->commands[$name];
    }

    public function run($failSilently = false) {
        foreach ($this->commands as $name => $command) {
            $this->runCommand($name, $command, $failSilently);
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getResult($name) {
        if(!isset($this->completedCommands[$name])) {
            throw new \Exception('No completed command found named ' . $name);
        }
        return $this->completedCommands[$name]->getResult();
    }

    public function getExceptionStack() {
        return $this->exceptionStack;
    }

    /**
     * @return ICommand[]
     */
    public function getCompletedCommands() {
        return array_values($this->completedCommands);
    }

    private function fillArguments(array $arguments) {
        foreach($arguments as $key => $argument) {
            if(isset($argument[0]) && $argument[0] == '_') {
                $name = ltrim($argument, '_');
                if(!isset($this->completedCommands[$name])) {
                    throw new \Exception('Could not find a complete command result named ' . $name);
                }
                $arguments[$key] = $this->completedCommands[$name]->getResult();
            }
        }
        return $arguments;
    }

    private function runCommand($name, ICommand $command, $failSilently) {
        try {
            $filledArguments = $this->fillArguments($command->getArguments());
            $command->run($filledArguments);
            $this->completedCommands[$name] = $command;
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
        /** @var ICommand[] $completedCommands */
        $completedCommands = array_reverse($this->completedCommands);
        foreach($completedCommands as $name => $completedCommand) {
            try {
                if($completedCommand->hasRollback()) {
                    $rollback = $completedCommand->getRollback();
                    $filledArguments = $this->fillArguments($rollback->getArguments());
                    $rollback->run($filledArguments);
                    $this->completedCommands[$name . '-rollback'] = $rollback;
                }
            }
            catch(\Exception $ex) {
                $this->exceptionStack[] = $ex;
            }
        }
    }
}