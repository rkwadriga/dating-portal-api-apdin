<?php declare(strict_types=1);
/**
 * Created 2021-11-26
 * Author Dmitry Kushneriov
 */

namespace App\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends AbstractUnitTest
{
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Application(static::$kernel);
        //$this->application->setAutoExit(true);
    }

    public function getCommandTester(string $commandName): CommandTester
    {
        $command = $this->application->find($commandName);
        return new CommandTester($command);
    }
}