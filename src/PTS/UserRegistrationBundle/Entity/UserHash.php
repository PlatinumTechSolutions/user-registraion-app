<?php

namespace PTS\UserRegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use PTS\UserRegistrationBundle\Exception\ValidationException;

/**
 * @ORM\Table(name="user_hash")
 * @ORM\Entity(repositoryClass="UserHashRepository")
 */
class UserHash
{
    const TYPE_EMAIL_CONFIRMATION = 'EMAIL_CONFIRMATION';
    const TYPE_PASSWORD_RESET     = 'PASSWORD_RESET';

    private $allowedTypeList = [
        self::TYPE_EMAIL_CONFIRMATION,
        self::TYPE_PASSWORD_RESET,
    ];

    /**
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userHashes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @return boolean
     * @throws ValidationException
     */
    public function validate()
    {
        if (in_array($this->type, $this->allowedTypeList, true) === false) {
            throw new ValidationException("That type is not in the allowed list: " . $this->type);
        }
        return true;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return UserHash
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return UserHash
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserHash
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get full name for user
     *
     * @return string
     */
    public function getUserFullName()
    {
        return $this->getUser()->getFullName();
    }
}
