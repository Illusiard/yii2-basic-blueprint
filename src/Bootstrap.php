<?php

namespace Illusiard\Yii2BasicBlueprint;

use Illusiard\Yii2BasicBlueprint\Support\BlueprintConfigWriterInterface;
use Illusiard\Yii2BasicBlueprint\Support\CommandRunnerInterface;
use Illusiard\Yii2BasicBlueprint\Support\PhpVersionResolverInterface;

final class Bootstrap
{
    public function __construct(
        private readonly CommandRunnerInterface $runner,
        private readonly PhpVersionResolverInterface $phpResolver,
        private readonly BlueprintConfigWriterInterface $configWriter,
    ) {}

    public function run(string $root): int
    {
        $appDir = $root . '/app';
        if (is_dir($appDir)) {
            return 1;
        }

        $phpVersion = $this->phpResolver->resolve();

        $this->configWriter->write($root . '/.blueprint.json', [
            'schemaVersion' => 1,
            'appDir' => 'app',
            'phpVersion' => $phpVersion,
        ]);

        $this->runner->run([
            'composer', 'create-project',
            '--prefer-dist', '--no-interaction', '--no-install',
            'yiisoft/yii2-app-basic', 'app',
        ], $root);

        $this->runner->run(['composer', 'config', 'platform.php', $phpVersion . '.0'], $appDir);
        $this->runner->run(['composer', 'update', '--lock', '--no-interaction'], $appDir);
        $this->runner->run(['composer', 'require', '--dev', '--no-interaction', 'illusiard/yii2-blueprint:^0.1'], $appDir);
        $this->runner->run(['composer', 'install', '--no-interaction'], $appDir);
        $this->runner->run(['composer', 'blueprint:init'], $appDir);

        return 0;
    }
}
