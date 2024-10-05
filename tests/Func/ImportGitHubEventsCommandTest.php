<?php

declare(strict_types=1);

namespace App\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportGitHubEventsCommandTest extends KernelTestCase
{
    public function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'date' => '2024-10-01',
            'hour' => '10',
            '--env' => 'test',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('2 events, 0 discarded, 0 errors.', $output);

        unlink('./2024-10-01-10.json.gz');
    }
}
