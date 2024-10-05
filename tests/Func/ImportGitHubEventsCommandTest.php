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
        $this->assertStringContainsString('3 events, 1 discarded, 0 errors.', $output);

        unlink('./2024-10-01-10.json.gz');
    }

    public function testInvalidDate(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);

        $commandTester->execute([
            'date' => '2024-10-01-10',
            'hour' => '10',
            '--env' => 'test',
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid date format. Please use Y-m-d format.', $output);
    }

    public function testInvalidHour(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:import-github-events');
        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);

        $commandTester->execute([
            'date' => '2024-10-01',
            'hour' => '25',
            '--env' => 'test',
        ]);

        $this->assertSame(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid hour format. Please provide an hour between 0 and 23.', $output);
    }
}
