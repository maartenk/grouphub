<?php

namespace AppBundle\Model;

/**
 * Class Membership
 */
class Membership
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';

    /**
     * @var string
     */
    private $role;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var User
     */
    private $user;

    /**
     * @param string $role
     * @param Group  $group
     * @param User   $user
     */
    public function __construct($role, Group $group = null, User $user = null)
    {
        $this->role = $role;
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
