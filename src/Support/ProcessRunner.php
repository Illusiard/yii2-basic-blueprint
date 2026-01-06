<?php

namespace Illusiard\Yii2BasicBlueprint\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final readonly class ProcessRunner implements CommandRunnerInterface
{
    public function __construct(
        private OutputInterface $output
    ) {
    }

    public function run(array $cmd, string $cwd): void
    {
        $p = new Process($cmd, $cwd);
        $p->setTimeout(null);

        $this->output->writeln('<comment>$ ' . implode(' ', $cmd) . '</comment>');
        $p->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$p->isSuccessful()) {
            throw new \RuntimeException('Command failed: ' . $p->getErrorOutput());
        }
    }
}
