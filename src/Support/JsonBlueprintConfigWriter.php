<?php

namespace Illusiard\Yii2BasicBlueprint\Support;

final class JsonBlueprintConfigWriter implements BlueprintConfigWriterInterface
{
    public function write(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Cannot encode json.');
        }
        if (file_put_contents($path, $json . PHP_EOL) === false) {
            throw new \RuntimeException("Cannot write file: {$path}");
        }
    }
}
