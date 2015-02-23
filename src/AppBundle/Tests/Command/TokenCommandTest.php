<?php

/**
 * This file is part of the FOSRestByExample package.
 *
 * (c) Santiago Diaz <santiago.diaz@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace AppBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Command\TokenCommand;

class TokenCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new TokenCommand());

        $command = $application->find('app:token:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'username' => 'admin',
                'password' => 'password',
            )
        );

        $this->assertRegExp('/WSSE/', $commandTester->getDisplay());
    }

    public function testFailExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new TokenCommand());

        $command = $application->find('app:token:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'username' => 'nonexistenuser',
                'password' => 'password',
            )
        );

        $this->assertRegExp('/User not found/', $commandTester->getDisplay());
    }

}
