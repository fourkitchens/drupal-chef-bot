<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JoliCode\Slack\Api\Model\ObjsChannel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChannelRepository")
 */
class Channel
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
    private $channelId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $topic;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="channels")
     */
    private $users;

    /**
     * Constructs a Channel object.
     *
     * @param object|null $channel
     *   A slack channel object
     */
    public function __construct($channel = NULL)
    {
        if ($channel) {
            $this->setChannelId($channel->id);
            $this->setName($channel->name);
            $this->setTopic($channel->topic->value ?? '');
        }
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): self
    {
        $this->channelId = $channelId;

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

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

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
            $user->addChannel($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeChannel($this);
        }

        return $this;
    }

    /**
     * @param $channel
     *   A slack channel object.
     *
     * @return $this
     */
    public function updateFromSlackItem($channel) :self {
        if ($this->getChannelId() !== $channel->id) {
            throw new \InvalidArgumentException(sprintf('Attempting to update a %s from slack with the wrong object', __CLASS__));
        }
        $this->setChannelId($channel->id);
        $this->setName($channel->name);
        $this->setTopic($channel->topic->value ?? '');
        return $this;
    }

    /**
     * Create a channel entity from a slack object.
     *
     * @param ObjsChannel $channel
     * @return Channel
     */
    public static function createFromSlackItem(ObjsChannel $channel) :Channel {
        $channel_entity = new static();
        $channel_entity->setChannelId($channel->getId());
        $channel_entity->setName($channel->getName());
        $channel_entity->setTopic($channel->getTopic()->getValue() ?? '');
        return $channel_entity;
    }
}
