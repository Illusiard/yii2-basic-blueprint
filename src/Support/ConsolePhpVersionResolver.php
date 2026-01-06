<?php

namespace Illusiard\Yii2BasicBlueprint\Support;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final readonly class ConsolePhpVersionResolver implements PhpVersionResolverInterface
{
    public function __construct(
        private OutputInterface $output
    ) {
    }

    public function resolve(): string
    {
        $input  = new ArgvInput();
        $helper = new QuestionHelper();

        $question = new ChoiceQuestion(
            'Target PHP version for the project:',
            [
                '8.4' => '8.4',
                '8.3' => '8.3 (default)',
                '8.2' => '8.2',
                '8.1' => '8.1',
            ],
            '8.3'
        );

        /** @var string $answer */
        $answer = $helper->ask($input, $this->output, $question);

        return $answer;
    }
}
