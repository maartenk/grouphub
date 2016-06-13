<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class LdapClientCompilerPass
 */
class LdapClientCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('app.ldap_fallback_clients')) {
            return;
        }

        $definition = $container->findDefinition('app.ldap_fallback_clients');

        $taggedServices = $container->findTaggedServiceIds('app.ldap_fallback_client');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('registerClient', [$attributes['alias'], new Reference($id)]);
            }
        }
    }
}
