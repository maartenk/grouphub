<?php

namespace AppBundle\Model;

/**
 *
 */
class Notification
{
    const TYPE_PROSPECT = 'prospect';

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $from;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var Group
     */
    private $group;

    /**
     * @param int       $id
     * @param User      $from
     * @param \DateTime $created
     * @param string    $type
     * @param string    $message
     * @param Group     $group
     */
    public function __construct(
        $id,
        User $from,
        \DateTime $created,
        $type = self::TYPE_PROSPECT,
        $message = '',
        Group $group = null
    ) {
        $this->id = $id;
        $this->from = $from;
        $this->created = $created;
        $this->type = $type;
        $this->message = $message;
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
