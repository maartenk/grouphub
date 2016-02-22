<?php

namespace AppBundle\Model;

use DateTime;
use Doctrine\Common\Comparable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Group
 */
class Group implements Comparable
{
    const TYPE_LDAP = 'ldap';
    const TYPE_FORMAL = 'formal';
    const TYPE_GROUPHUB = 'grouphub';

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
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var int
     */
    private $parentId;

    /**
     * @var DateTime
     */
    private $timeStamp;

    /**
     * @param int      $id
     * @param string   $reference
     * @param string   $name
     * @param string   $description
     * @param string   $type
     * @param User     $owner
     * @param int      $parentId
     * @param DateTime $timeStamp
     */
    public function __construct(
        $id = null,
        $reference = '',
        $name = '',
        $description = '',
        $type = '',
        User $owner = null,
        $parentId = null,
        DateTime $timeStamp = null
    ) {
        $this->id = $id;
        $this->reference = $reference;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->owner = $owner;
        $this->parentId = $parentId;
        $this->timeStamp = $timeStamp;
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
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        if (!$this->owner) {
            return null;
        }

        return $this->owner->getId();
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $id
     */
    public function setParentId($id)
    {
        $this->parentId = $id;
    }

    /**
     * @return DateTime
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @inheritdoc
     *
     * @param Group $other
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
     * @param Group $other
     *
     * @return bool
     */
    public function equals($other)
    {
        if ($other->getReference() !== $this->reference) {
            return false;
        }

        if ($other->getName() !== $this->name) {
            return false;
        }

        if ($other->getDescription() !== $this->description) {
            return false;
        }

        return true;
    }
}
