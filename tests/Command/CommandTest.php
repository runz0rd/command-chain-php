<?php
/**
 * Created by PhpStorm.
 * User: milos
 * Date: 25/01/17
 * Time: 01:03
 */


class CommandTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Command\ICommand
     */
    private $command;

    public function setUp()
    {
        $mockCommand = new MockCommand();
        $this->command = new Command\Command('testCommand', $mockCommand, 'testMethod', array('test'));
        parent::setUp();
    }

    /**
     * @expectedException Exception
     */
    public function testConstructFail() {
        $mockCommand = new MockCommand();
        $command = new Command\Command('testCommand', $mockCommand, 'badMethod', array('test'));
    }

    public function testRun() {
        $this->command->run();
        $this->assertEquals('test', $this->command->getResult());
    }

    public function testRunWithArguments() {
        $this->command->run(array('asdf'));
        $this->assertEquals('asdf', $this->command->getResult());
    }
}
