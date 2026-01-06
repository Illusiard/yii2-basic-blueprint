<?php

namespace Illusiard\Yii2BasicBlueprint\Support;

interface CommandRunnerInterface
{
    public function run(array $cmd, string $cwd): void;
}
