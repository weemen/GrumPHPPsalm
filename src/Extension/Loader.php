<?php

declare(strict_types=1);

namespace Weemen\GrumPHPPsalm\Extension;

use Weemen\GrumPHPPsalm\Task\Psalm;

class Loader implements \GrumPHP\Extension\ExtensionInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->register('task.psalm', Psalm::class)
            ->addArgument($container->get('config'))
            ->addArgument($container->get('process_builder'))
            ->addArgument($container->get('async_process_runner'))
            ->addArgument($container->get('formatter.raw_process'))
            ->addTag('grumphp.task', ['config' => 'psalm']);
    }
}
