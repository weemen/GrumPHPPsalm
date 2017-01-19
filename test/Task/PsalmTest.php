<?php

namespace Weemen\GrumPHPPsalm;


use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Process\AsyncProcessRunner;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Weemen\GrumPHPPsalm\Task\Psalm;

class PsalmTest extends \PHPUnit_Framework_TestCase
{

    private function createPsalmTask()
    {
        $container = $this->createMock(ContainerInterface::class);
        $processBuilder = $this->getMockBuilder(ProcessBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $asyncProcessRunner = $this->getMockBuilder(AsyncProcessRunner::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formatter = $this->createMock(RawProcessFormatter::class);

        $grumPHP = new GrumPHP($container);
        return new Psalm($grumPHP, $processBuilder, $asyncProcessRunner, $formatter);
    }

    /**
     * @test
     */
    public function is_has_default_options_configured()
    {
        $psalmTask  = $this->createPsalmTask();
        $resolver   = $psalmTask->getConfigurableOptions();
        $this->assertTrue($resolver->hasDefault('config'));
    }


    /**
     * @test
     */
    public function it_can_run_in_pre_commit_context()
    {
        $context = $this->getMockBuilder(GitPreCommitContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $psalmTask  = $this->createPsalmTask();
        $this->assertTrue($psalmTask->canRunInContext($context));
    }

    /**
     * @test
     */
    public function it_can_run_in_run_context()
    {
        $context = $this->getMockBuilder(RunContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $psalmTask  = $this->createPsalmTask();
        $this->assertTrue($psalmTask->canRunInContext($context));
    }

    /**
     * @test
     */
    public function it_will_automaticly_pass_when_not_in_pre_commit_context()
    {
        $filesCollection = $this->getMockBuilder(FilesCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesCollection->expects($this->once())
            ->method('name')
            ->willReturn(new FilesCollection(['1.php']) );

        $context = $this->getMockBuilder(RunContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('getFiles')
            ->willReturn($filesCollection);

        $psalmTask  = $this->createPsalmTask();
        $result     = $psalmTask->run($context);

        $this->assertEquals(TaskResult::SKIPPED,$result->getResultCode());
    }

    /**
     * @test
     */
    public function it_will_automaticly_pass_when_there_are_no_files_to_check()
    {
        $filesCollection = $this->getMockBuilder(FilesCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesCollection->expects($this->once())
            ->method('name')
            ->willReturn(new FilesCollection([]) );

        $context = $this->getMockBuilder(GitPreCommitContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->once())
            ->method('getFiles')
            ->willReturn($filesCollection);

        $psalmTask  = $this->createPsalmTask();
        $result     = $psalmTask->run($context);

        $this->assertEquals(TaskResult::SKIPPED,$result->getResultCode());
    }

//    /**
//     * @test
//     */
//    public function it_will_run_process_with_success_handle()
//    {
//        $filesCollection = $this->getMockBuilder(FilesCollection::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $filesCollection->expects($this->once())
//            ->method('name')
//            ->willReturn(new FilesCollection(['1.php']) );
//
//        $context = $this->getMockBuilder(GitPreCommitContext::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $context->expects($this->once())
//            ->method('getFiles')
//            ->willReturn($filesCollection);
//
//        $psalmTask  = $this->createPsalmTask();
//        $result     = $psalmTask->run($context);
//
//        $this->assertTrue($result->isPassed());
//    }

}
