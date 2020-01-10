<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JoliCode\Slack\Api\Model\ObjsUser;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16, unique=true)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $realName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Channel", inversedBy="users")
     */
    private $channels;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserID(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * @return Collection|Channel[]
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): self
    {
        if (!$this->channels->contains($channel)) {
            $this->channels[] = $channel;
        }

        return $this;
    }

    public function removeChannel(Channel $channel): self
    {
        if ($this->channels->contains($channel)) {
            $this->channels->removeElement($channel);
        }

        return $this;
    }

    /**
     * @param ObjsUser $user
     *
     * @param Team|null $team
     * @return static
     */
    public static function createFromSlackItem(ObjsUser $user, ?Team $team = null): User {
        $user_entity = new static();
        $user_entity->setUserID($user->getId());
        $user_entity->setIsAdmin($user->getIsAdmin());
        $user_entity->setIsDeleted($user->getDeleted());
        $user_entity->setName($user->getName());
        $user_entity->setRealName($user->getRealName());
        $user_entity->setTeam($team);
        $user_entity->setPoints(0);
        return $user_entity;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }
}
