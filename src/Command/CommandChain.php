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
     * @var boolean
     */
    private $rollbackSilentFail;

    /**
     * CommandChain constructor.
     * @param $rollbackSilentFail
     */
    public function __construct($rollbackSilentFail = true) {
        $this->rollbackSilentFail = $rollbackSilentFail;
    }

    /**
     * @param string $name
     * @param ICommand $command
     */
    public function add($name, ICommand $command) {
        $this->commands[$name] = $command;
    }

    public function run() {
        foreach($this->commands as $name => $command) {
            $filledArguments = $this->fillArguments($command->getArguments());
            $this->runCommand($name, $command, $filledArguments);
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

    private function fillArguments(array $arguments) {
        foreach($arguments as $key => $argument) {
            if(isset($argument[0]) && $argument[0] == '$') {
                $name = ltrim($argument, '$');
                if(!isset($this->completedCommands[$name])) {
                    throw new \Exception('Could not find a complete command result named ' . $name);
                }
                $arguments[$key] = $this->completedCommands[$name]->getResult();
            }
        }

        return $arguments;
    }

    private function runCommand($name, ICommand $command, $filledArguments) {
        try {
            $command->run($filledArguments);
            $this->completedCommands[$name] = $command;
        }
        catch(\Exception $ex) {
            $this->rollback();
            throw $ex;
        }
    }

    private function rollback() {
        foreach($this->completedCommands as $completedCommand) {
            try {
                $completedCommand->getRollback()->run();
            }
            catch(\Exception $ex) {
                if(!$this->rollbackSilentFail) {
                    throw $ex;
                }
            }
        }
    }
}