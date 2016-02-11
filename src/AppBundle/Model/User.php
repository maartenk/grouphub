<?php

namespace AppBundle\Model;

use Doctrine\Common\Comparable;
use Hslavich\SimplesamlphpBundle\Security\Core\User\SamlUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 */
class User implements Comparable, UserInterface, EquatableInterface, SamlUserInterface
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
     * @var array
     */
    private $samlAttributes;

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
    public function getName()
    {
        return $this->firstName . ' ' . $this->lastName;
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

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->getLoginName();
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        // nothing here..
    }

    /**
     * @inheritdoc
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setSamlAttributes(array $attributes)
    {
        $this->samlAttributes = $attributes;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        if (isset($this->samlAttributes['urn:mace:dir:attribute-def:mail'][0])) {
            return $this->samlAttributes['urn:mace:dir:attribute-def:mail'][0];
        }

        return '';
    }
}
