<?php

declare(strict_types=1);

namespace Weemen\GrumPHPPsalm\Extension;

class Loader implements \GrumPHP\Extension\ExtensionInterface
{

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->register('task.psalm', PhpCsAutoFixer::class)
            ->addArgument($container->get('config'))
            ->addTag('grumphp.task', ['config' => 'psalm']);
    }
}
