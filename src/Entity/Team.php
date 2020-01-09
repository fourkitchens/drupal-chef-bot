<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JoliCode\Slack\Api\Model\ObjsTeam;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 */
class Team
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
    private $teamId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="team", orphanRemoval=true)
     */
    private $users;

  /**
   * Constructs a Team object.
   *
   * @param object|null $team
   *   A slack team object.
   */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    public function setTeamId(string $teamId): self
    {
        $this->teamId = $teamId;

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

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setTeam($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getTeam() === $this) {
                $user->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @param $team
     *   A slack team object.
     *
     * @return $this
     */
    public function updateFromSlackItem($team) :self {
        if ($this->getTeamId() !== $team->id) {
            throw new \InvalidArgumentException(sprintf('Attempting to update a %s from slack with the wrong object', __CLASS__));
        }
        $this->setName($team->name);
        $this->setDomain($team->domain);
        return $this;
    }

    /**
     * @param ObjsTeam $team
     *
     * @return static
     */
    public static function createFromSlackItem(ObjsTeam $team) {
        $team_entity = new static();
        $team_entity->setTeamId($team->getId());
        $team_entity->setName($team->getName());
        $team_entity->setDomain($team->getDomain());
        return $team_entity;
    }
}
