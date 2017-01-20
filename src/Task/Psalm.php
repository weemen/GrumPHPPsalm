<?php

declare(strict_types=1);

namespace Weemen\GrumPHPPsalm\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Process\AsyncProcessRunner;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Psalm extends AbstractExternalTask
{

    /**
     * @param GrumPHP                   $grumPHP
     * @param ProcessBuilder            $processBuilder
     * @param AsyncProcessRunner        $processRunner
     * @param ProcessFormatterInterface $formatter
     */
    public function __construct(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        AsyncProcessRunner $processRunner,
        RawProcessFormatter $formatter
    ) {
        $this->processBuilder = $processBuilder;
        $this->processRunner = $processRunner;
        $this->formatter = $formatter;
        $this->grumPHP = $grumPHP;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'psalm';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => 'psalm.xml',
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        return $resolver;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResult
     */
    public function run(ContextInterface $context): TaskResult
    {
        $files  = $context->getFiles()->name('*.php');

        if (! $context instanceof GitPreCommitContext || 0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $processes = $this->buildProcesses($files);
        $this->processRunner->run($processes);

        foreach ($processes as $process) {
            if (!$process->isSuccessful()) {
                return TaskResult::createFailed($this, $context, $this->formatter->format($process));

            }
        }

        return $this->handleSuccesfullResult($context);
    }

    /**
     * @param FilesCollection $files
     *
     * @return array
     */
    protected function buildProcesses(FilesCollection $files): array
    {
        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('psalm');
        $arguments->addOptionalArgumentWithSeparatedValue('--config', $config['config']);
        foreach ($files as $file) {
            $arguments->add($file);
            $processes[] = $this->processBuilder->buildProcess($arguments);
        }

        return $processes;
    }

    /**
     * @param GitPreCommitContext $context
     *
     * @return TaskResult
     */
    protected function handleSuccesfullResult(GitPreCommitContext $context): TaskResult
    {
        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param GitPreCommitContext $context
     * @param                     $process
     *
     * @return TaskResult
     */
    protected function handleFailedResult(GitPreCommitContext $context, $process): TaskResult
    {
        return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }
}
