<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 01:03
 */

class CommandChainTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Command\CommandChain
     */
    private $commandChain;

    public function setUp()
    {
        $this->commandChain = new \Command\CommandChain();
        parent::setUp();
    }

    public function testRun() {
        $this->commandChain->add('command1', new MockCommand(), 'testMethod', array('test1'));
        $this->commandChain->add('command2', new MockCommand(), 'testMethod', array('test2'));
        $this->commandChain->add('command3', new MockCommand(), 'testMethod', array('test3'));
        $this->commandChain->run();

        $this->assertEquals('test1', $this->commandChain->getResult('command1'));
        $this->assertEquals('test2', $this->commandChain->getResult('command2'));
        $this->assertEquals('test3', $this->commandChain->getResult('command3'));
    }

    public function testRunWithRollback() {
        $this->commandChain->add('command1', new MockCommand(), 'testMethod', array('test1'))
            ->addRollback(new MockCommand(true), 'testMethod', array('rollback1'));
        $this->commandChain->add('command2', new MockCommand(), 'testMethod', array('test2'))
            ->addRollback(new MockCommand(true), 'testMethod', array('rollback2'));
        $this->commandChain->add('command3', new MockCommand(true), 'testMethod', array('test3'));
        $this->commandChain->run(true);

        $exceptionStack = $this->commandChain->getExceptionStack();
        $this->assertEquals('test3', $exceptionStack[0]->getMessage());
        $this->assertEquals('rollback2', $exceptionStack[1]->getMessage());
        $this->assertEquals('rollback1', $exceptionStack[2]->getMessage());
    }

    public function testRunWithPlaceHolders() {
        $this->commandChain->add('command1', new MockCommand(), 'testMethod', array('test1'));
        $this->commandChain->add('command2', new MockCommand(), 'testMethod', array('_command1'));
        $this->commandChain->add('command3', new MockCommand(), 'testMethod', array('_command2'));
        $this->commandChain->run();

        $this->assertEquals('test1', $this->commandChain->getResult('command1'));
        $this->assertEquals('test1', $this->commandChain->getResult('command2'));
        $this->assertEquals('test1', $this->commandChain->getResult('command3'));
    }

    public function testRunWithRollbackPlaceHolders() {
        $this->commandChain->add('command1', new MockCommand(), 'testMethod', array('test1'))
            ->addRollback(new MockCommand(), 'testMethod', array('_command1'));
        $this->commandChain->add('command2', new MockCommand(), 'testMethod', array('test2'))
            ->addRollback(new MockCommand(), 'testMethod', array('_command2'));
        $this->commandChain->add('command3', new MockCommand(true), 'testMethod', array('test3'))
            ->addRollback(new MockCommand(), 'testMethod', array('_command3'));
        $this->commandChain->run(true);

        $completedCommands = $this->commandChain->getCompletedCommands();

        $this->assertEquals('test1', $completedCommands[0]->getResult());
        $this->assertEquals('test2', $completedCommands[1]->getResult());
        $this->assertEquals('test2', $completedCommands[2]->getResult());
        $this->assertEquals('test1', $completedCommands[3]->getResult());
    }

    /**
     * @expectedException Exception
     */
    public function testRunWithPlaceHoldersFail() {
        $this->commandChain->add('command1', new MockCommand(), 'testMethod', array('test1'));
        $this->commandChain->add('command2', new MockCommand(), 'testMethod', array('_command3'));
        $this->commandChain->add('command3', new MockCommand(), 'testMethod', array('_command2'));
        $this->commandChain->run();
    }
}
