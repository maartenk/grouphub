<?php

namespace AppBundle\Model;

use Doctrine\Common\Comparable;

/**
 * Class User
 */
class User implements Comparable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $loginName;

    /**
     * @param int    $id
     * @param string $reference
     * @param string $firstName
     * @param string $lastName
     * @param string $loginName
     */
    public function __construct($id = null, $reference = '', $firstName = '', $lastName = '', $loginName = '')
    {
        $this->id = $id;
        $this->reference = $reference;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->loginName = $loginName;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getLoginName()
    {
        return $this->loginName;
    }

    /**
     * @inheritdoc
     *
     * @param User $other
     */
    public function compareTo($other)
    {
        if ($other->getReference() == $this->reference) {
            return 0;
        }

        if ($other->getReference() < $this->reference) {
            return 1;
        }

        return -1;
    }

    /**
     * @param User $other
     *
     * @return bool
     */
    public function equals($other)
    {
        if ($other->getReference() !== $this->reference) {
            return false;
        }

        if ($other->getFirstName() !== $this->firstName) {
            return false;
        }

        if ($other->getLastName() !== $this->lastName) {
            return false;
        }

        if ($other->getLoginName() !== $this->loginName) {
            return false;
        }

        return true;
    }
}
