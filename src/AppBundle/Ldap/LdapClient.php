<?php

namespace AppBundle\Ldap;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\LdapClientInterface;

/**
 * Class LdapClient
 */
class LdapClient implements LdapClientInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $useSsl;

    /**
     * @var bool
     */
    private $useStartTls;

    /**
     * @var bool
     */
    private $optReferrals;

    /**
     * @var
     */
    private $connection;

    /**
     * @var string
     */
    private $dn;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $isBound = false;

    /**
     * @param string $host
     * @param int    $port
     * @param string $dn
     * @param string $password
     * @param int    $version
     * @param bool   $useSsl
     * @param bool   $useStartTls
     * @param bool   $optReferrals
     */
    public function __construct(
        $host = null,
        $port = 389,
        $dn = null,
        $password = null,
        $version = 3,
        $useSsl = false,
        $useStartTls = false,
        $optReferrals = false
    ) {
        if (!extension_loaded('ldap')) {
            throw new LdapException('The ldap module is needed.');
        }

        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
        $this->useSsl = (bool) $useSsl;
        $this->useStartTls = (bool) $useStartTls;
        $this->optReferrals = (bool) $optReferrals;

        $this->dn = $dn;
        $this->password = $password;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * {@inheritdoc}
     */
    public function bind($dn = null, $password = null)
    {
        if (!$this->connection) {
            $this->connect();
        }

        if (false === @ldap_bind($this->connection, $dn, $password)) {
            throw new ConnectionException(ldap_error($this->connection));
        }

        $this->isBound = true;
    }

    /**
     * {@inheritdoc}
     */
    public function find($dn, $query, $filter = '*', $sort = null)
    {
        if (!$this->isBound) {
            $this->bind($this->dn, $this->password);
        }

        if (!is_array($filter)) {
            $filter = [$filter];
        }

        $search = ldap_search($this->connection, $dn, $query, $filter);

        if ($sort !== null) {
            ldap_sort($this->connection, $search, $sort);
        }

        $infos = ldap_get_entries($this->connection, $search);

        if (0 === $infos['count']) {
            return [];
        }

        return $infos;
    }

    /**
     * @param string $dn
     * @param array  $data
     */
    public function add($dn, array $data)
    {
        if (!$this->isBound) {
            $this->bind($this->dn, $this->password);
        }

        ldap_add($this->connection, $dn, $data);
    }

    /**
     * @param string $dn
     */
    public function delete($dn)
    {
        if (!$this->isBound) {
            $this->bind($this->dn, $this->password);
        }

        ldap_delete($this->connection, $dn);
    }

    /**
     * @param string $dn
     * @param array  $data
     */
    public function addAttribute($dn, array $data)
    {
        if (!$this->isBound) {
            $this->bind($this->dn, $this->password);
        }

        ldap_mod_add($this->connection, $dn, $data);
    }

    /**
     * @param string $dn
     * @param array  $data
     */
    public function deleteAttribute($dn, array $data)
    {
        if (!$this->isBound) {
            $this->bind($this->dn, $this->password);
        }

        ldap_mod_del($this->connection, $dn, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function escape($subject, $ignore = '', $flags = 0)
    {
        $value = ldap_escape($subject, $ignore, $flags);

        // Per RFC 4514, leading/trailing spaces should be encoded in DNs, as well as carriage returns.
        if ((int) $flags & LDAP_ESCAPE_DN) {
            if (!empty($value) && $value[0] === ' ') {
                $value = '\\20' . substr($value, 1);
            }
            if (!empty($value) && $value[strlen($value) - 1] === ' ') {
                $value = substr($value, 0, -1) . '\\20';
            }
            $value = str_replace("\r", '\0d', $value);
        }

        return $value;
    }

    private function connect()
    {
        if (!$this->connection) {
            $host = $this->host;

            if ($this->useSsl) {
                $host = 'ldaps://' . $host;
            }

            $this->connection = ldap_connect($host, $this->port);

            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->version);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, $this->optReferrals);

            if ($this->useStartTls) {
                ldap_start_tls($this->connection);
            }
        }
    }

    private function disconnect()
    {
        if ($this->connection && is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }

        $this->connection = null;
    }
}
