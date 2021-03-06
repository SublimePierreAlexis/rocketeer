<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Tasks;

use Prophecy\Argument;
use Rocketeer\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
    public function testCanIgniteConfigurationOnWindows()
    {
        $this->mockBasepath('E:\workspace\test');
        $prophecy = $this->bindFilesystemProphecy();

        $this->pretendTask('Ignite')->execute();

        $prophecy->append('E:/workspace/test/.env', Argument::any())->shouldHaveBeenCalled();
        $prophecy->createDir('E:/workspace/test/.rocketeer/config', Argument::any())->shouldHaveBeenCalled();
    }

    public function testCanIgniteConfiguration()
    {
        $this->mockAnswers([
            'name' => 'foobar',
            'Where is' => 'git@github.com/rocketeers/website.git',
            'SSH key' => true,
        ]);

        $dotenvPath = $this->paths->getDotenvPath();
        $this->files->put($dotenvPath, 'FOO="bar"' . PHP_EOL);

        $this->task('Ignite')->execute();

        $config = $this->files->read($this->paths->getConfigurationPath().'/config.php');
        $dotenv = $this->files->read($dotenvPath);

        $this->assertContains("'application_name' => 'foobar'", $config);
        $this->assertContains('FOO="bar"', $dotenv);
        $this->assertContains('VCS_REPOSITORY="git@github.com/rocketeers/website.git"', $dotenv);
    }

    public function testCanIgniteStubs()
    {
        $this->replicateConfiguration();

        $this->task('Ignite')->fire();
        $path = $this->paths->getUserlandPath();

        $serviceProviderContents = $this->files->read($path.'/DeploymentsServiceProvider.php');
        $this->assertContains('namespace Deployments', $serviceProviderContents);
        $this->assertContains('class DeploymentsServiceProvider', $serviceProviderContents);
    }
}
