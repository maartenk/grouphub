<?php

namespace AppBundle\Ldap;

/**
 * Class LdapClients
 */
class LdapClients
{
    /**
     * @var LdapClient[]
     */
    private $clients = [];

    /**
     * @param string     $alias
     * @param LdapClient $client
     */
    public function registerClient($alias, LdapClient $client)
    {
        $this->clients[$alias] = $client;
    }

    /**
     * @param string $alias
     *
     * @return LdapClient
     */
    public function getClient($alias)
    {
        if (!array_key_exists($alias, $this->clients)) {
            throw new \InvalidArgumentException('Unknown client alias');
        }

        return $this->clients[$alias];
    }
}
