<?php

namespace AppBundle\DependencyInjection;

use AppBundle\Ldap\LdapClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AppExtension
 */
class AppExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $param = $container->getParameter('ldap_fallback');

        foreach ($param as $alias => $params) {
            $definition = new Definition(LdapClient::class, [$params]);
            $definition->addTag('app.ldap_fallback_client', array('alias' => $alias));

            $container->setDefinition('app.ldap_fallback_client.' . $alias, $definition);
        }
    }
}
