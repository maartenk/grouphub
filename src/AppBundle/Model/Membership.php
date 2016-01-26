<?php

namespace AppBundle\Model;

/**
 * Class Membership
 */
class Membership
{
    /**
     * @var string
     */
    private $role;

    /**
     * @var Group
     */
    private $group;

    /**
     * @param string $role
     * @param Group  $group
     */
    public function __construct($role, Group $group)
    {
        $this->role = $role;
        $this->group = $group;
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
}
