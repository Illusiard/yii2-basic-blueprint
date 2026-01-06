<?php

namespace Illusiard\Yii2BasicBlueprint\Support;

interface BlueprintConfigWriterInterface
{
    public function write(string $path, array $data): void;
}
