<?php

namespace AppBundle\Ldap;

use AppBundle\Model\Group;
use AppBundle\Model\User;
use AppBundle\Sequence;
use AppBundle\SynchronizableSequence;
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
    private $isBinded = false;

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

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence
     */
    public function findUsers($offset, $limit)
    {
        if (!$this->isBinded) {
            $this->bind($this->dn, $this->password);
        }

        // @todo: inject DN
        $data = $this->find('ou=Users,ou=SURFUni,dc=surfuni,dc=org', 'cn=*', '*', '');

        if (empty($data)) {
            return new Sequence([]);
        }

        $users = $this->denormalizeUsers($data);

        // @todo: use actual offset/limit
        $users = array_slice($users, $offset, $limit);

        return new Sequence($users);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Sequence
     */
    public function findGroups($offset, $limit)
    {
        if (!$this->isBinded) {
            $this->bind($this->dn, $this->password);
        }

        // @todo: inject DN
        $data = $this->find('ou=Formalgroups,dc=surfuni,dc=org', 'cn=*', '*', '');

        if (empty($data)) {
            return new Sequence([]);
        }

        $groups = $this->denormalizeGroups($data);

        // @todo: use actual offset/limit
        $groups = array_slice($groups, $offset, $limit);

        return new Sequence($groups);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return SynchronizableSequence
     */
    public function findGrouphubGroups($offset, $limit)
    {
        if (!$this->isBinded) {
            $this->bind($this->dn, $this->password);
        }

        // @todo: inject DN
        $data = $this->find('ou=Grouphub,dc=surfuni,dc=org', 'cn=*', '*', '');

        if (empty($data)) {
            return new SynchronizableSequence([]);
        }

        $groups = $this->denormalizeGrouphubGroups($data);

        // @todo: use actual offset/limit
        $groups = array_slice($groups, $offset, $limit);

        return new SynchronizableSequence($groups);
    }

    /**
     * @param array $users
     *
     * @return User[]
     */
    private function denormalizeUsers(array $users)
    {
        $result = [];
        for ($i = 0; $i < $users['count']; $i++) {
            $user = $users[$i];

            $result[] = new User(
                null, $user['dn'], $user['givenname'][0], $user['sn'][0], $user['uid'][0]
            );
        }

        return $result;
    }

    /**
     * @param array $groups
     *
     * @return User[]
     */
    private function denormalizeGroups(array $groups)
    {
        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null, $group['dn'], $group['cn'][0], '', 'ldap', 1
            );
        }

        return $result;
    }

    /**
     * @param array $groups
     *
     * @return User[]
     */
    private function denormalizeGrouphubGroups(array $groups)
    {
        $result = [];
        for ($i = 0; $i < $groups['count']; $i++) {
            $group = $groups[$i];

            $result[] = new Group(
                null, $group['dn'], $group['cn'][0], ''
            );
        }

        return $result;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    public function normalizeGroup(Group $group)
    {
        return [
            'cn'          => $group->getName(),
            'objectClass' => 'groupOfNames',  // @todo: inject??
            'member'      => '',
        ];
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
    }

    /**
     * {@inheritdoc}
     */
    public function find($dn, $query, $filter = '*', $sort = null)
    {
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

    /**
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group)
    {
        $dn = 'cn=' . $group->getName() . ',ou=Grouphub,dc=surfuni,dc=org'; // @todo: inject
        $group->setReference($dn);

        $data = $this->normalizeGroup($group);

        if (!$this->isBinded) {
            $this->bind($this->dn, $this->password);
        }

        ldap_add($this->connection, $group->getReference(), $data);

        return $group;
    }

    /**
     * @param string $groupReference
     * @param Group  $group
     */
    public function updateGroup($groupReference, Group $group)
    {
        // Not supported...
    }

    /**
     * @param string $groupReference
     */
    public function removeGroup($groupReference)
    {
        if (!$this->isBinded) {
            $this->bind($this->dn, $this->password);
        }

        ldap_delete($this->connection, $groupReference);
    }
}
