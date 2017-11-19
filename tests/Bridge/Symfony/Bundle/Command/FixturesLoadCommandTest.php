<?php

declare(strict_types=1);

namespace Bab\Datagen\Tests\Bridge\Symfony\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class FixturesLoadCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        self::bootKernel();

        $application = new Application(static::$kernel);
        $application->setCatchExceptions(false);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'datagen:fixtures:load']);

        $this->assertSame("Coucou\n", $tester->getDisplay());
    }
}
