<?php

namespace Weemen\GrumPHPPsalm\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Psalm extends AbstractExternalTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'psalm';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
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
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResult
     */
    public function run(ContextInterface $context)
    {
        $files  = $context->getFiles()->name('*.php');

        if (! $context instanceof GitPreCommitContext || 0 === count($files)) {
            return TaskResult::createPassed($this, $context);
        }

        $processes = $this->buildProcesses($files);
        $this->processRunner->run($processes);

        foreach ($processes as $process) {
            if (!$process->isSuccessful()) {
                $hasErrors = true;
                $messages[] = $this->formatter->format($process);
                $suggestions[] = $this->formatter->formatSuggestion($process);
            }
        }

        return ($hasErrors) ? $this->handleFailedResult($context, $messages, $suggestions) : $this->handleSuccesfullResult($context);
    }

    /**
     * @param FilesCollection $files
     *
     * @return array
     */
    protected function buildProcesses(FilesCollection $files)
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
    protected function handleSuccesfullResult(GitPreCommitContext $context)
    {
        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param GitPreCommitContext $context
     * @param array               $messages
     * @param array               $suggestions
     *
     * @return TaskResult
     */
    protected function handleFailedResult(GitPreCommitContext $context, array $messages, array $suggestions)
    {
        return TaskResult::createFailed(
            $this,
            $context,
            $this->formatter->formatErrorMessage($messages, $suggestions)
        );
    }
}
