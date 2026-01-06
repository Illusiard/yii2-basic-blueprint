<?php

namespace Illusiard\Yii2BasicBlueprint\Tests\Unit;

use Illusiard\Yii2BasicBlueprint\Bootstrap;
use Illusiard\Yii2BasicBlueprint\Support\BlueprintConfigWriterInterface;
use Illusiard\Yii2BasicBlueprint\Support\CommandRunnerInterface;
use Illusiard\Yii2BasicBlueprint\Support\PhpVersionResolverInterface;
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
    public function testRunWritesBlueprintConfigAndRunsComposerCommands(): void
    {
        $tmp = sys_get_temp_dir() . '/yii2-basic-blueprint-test-' . bin2hex(random_bytes(4));
        mkdir($tmp, 0777, true);

        $runner = new class implements CommandRunnerInterface {
            public array $calls = [];
            public function run(array $cmd, string $cwd): void
            {
                $this->calls[] = ['cmd' => $cmd, 'cwd' => $cwd];
            }
        };

        $resolver = new class implements PhpVersionResolverInterface {
            public function resolve(): string { return '8.3'; }
        };

        $writer = new class implements BlueprintConfigWriterInterface {
            public array $writes = [];
            public function write(string $path, array $data): void
            {
                $this->writes[] = ['path' => $path, 'data' => $data];
                // Для smoke-проверки формата можно реально записать
                file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
            }
        };

        $bootstrap = new Bootstrap($runner, $resolver, $writer);
        $code = $bootstrap->run($tmp);

        self::assertSame(0, $code);

        // 1) Конфиг записан
        self::assertFileExists($tmp . '/.blueprint.json');
        $cfg = json_decode((string)file_get_contents($tmp . '/.blueprint.json'), true);
        self::assertIsArray($cfg);
        self::assertSame(1, $cfg['schemaVersion']);
        self::assertSame('app', $cfg['appDir']);
        self::assertSame('8.3', $cfg['phpVersion']);

        // 2) Команды вызваны в нужном порядке и с нужными cwd
        $appDir = $tmp . '/app';

        self::assertCount(6, $runner->calls);

        self::assertSame($tmp, $runner->calls[0]['cwd']);
        self::assertSame(['composer','create-project','--prefer-dist','--no-interaction','--no-install','yiisoft/yii2-app-basic','app'], $runner->calls[0]['cmd']);

        self::assertSame($appDir, $runner->calls[1]['cwd']);
        self::assertSame(['composer','config','platform.php','8.3.0'], $runner->calls[1]['cmd']);

        self::assertSame(['composer','update','--lock','--no-interaction'], $runner->calls[2]['cmd']);
        self::assertSame(['composer','require','--dev','--no-interaction','illusiard/yii2-blueprint:^0.1'], $runner->calls[3]['cmd']);
        self::assertSame(['composer','install','--no-interaction'], $runner->calls[4]['cmd']);
        self::assertSame(['composer','blueprint:init'], $runner->calls[5]['cmd']);
    }

    public function testRunFailsIfAppDirAlreadyExists(): void
    {
        $tmp = sys_get_temp_dir() . '/yii2-basic-blueprint-test-' . bin2hex(random_bytes(4));
        mkdir($tmp . '/app', 0777, true);

        $runner = new class implements CommandRunnerInterface {
            public array $calls = [];
            public function run(array $cmd, string $cwd): void { $this->calls[] = [$cmd, $cwd]; }
        };
        $resolver = new class implements PhpVersionResolverInterface {
            public function resolve(): string { return '8.3'; }
        };
        $writer = new class implements BlueprintConfigWriterInterface {
            public function write(string $path, array $data): void {}
        };

        $bootstrap = new Bootstrap($runner, $resolver, $writer);
        $code = $bootstrap->run($tmp);

        self::assertSame(1, $code);
        self::assertSame([], $runner->calls, 'No commands must be executed if app/ already exists.');
    }
}
