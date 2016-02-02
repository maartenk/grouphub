<?php

namespace AppBundle\Model;

use DateTime;
use Doctrine\Common\Comparable;

/**
 * Class Group
 */
class Group implements Comparable
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
     * @var int
     */
    private $ownerId;

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
     * @param int      $ownerId
     * @param int      $parentId
     * @param DateTime $timeStamp
     */
    public function __construct(
        $id = null,
        $reference = '',
        $name = '',
        $description = '',
        $type = '',
        $ownerId = null,
        $parentId = null,
        DateTime $timeStamp = null
    ) {
        $this->id = $id;
        $this->reference = $reference;
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->ownerId = $ownerId;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
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
