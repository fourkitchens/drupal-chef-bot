<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\Table(name="message",indexes={@ORM\Index(name="message_id", columns={"ts", "channel_id"})})
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Channel")
     * @ORM\JoinColumn(nullable=false)
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=4096)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $ts;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Message", inversedBy="replies")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="parent")
     */
    private $replies;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getTs(): ?string
    {
        return $this->ts;
    }

    public function setTs(string $ts): self
    {
        $this->ts = $ts;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setParent($this);
        }

        return $this;
    }

    public function removeReply(self $reply): self
    {
        if ($this->replies->contains($reply)) {
            $this->replies->removeElement($reply);
            // set the owning side to null (unless already changed)
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @param Channel $channel
     * @param User $user
     * @param string $ts
     * @param string $text
     * @param Message|null $parent
     * @return Message
     */
    public static function create(Channel $channel, User $user, string $ts, string $text, ?Message $parent = null): Message {
        $message = new static();
        $message->setChannel($channel);
        $message->setTs($ts);
        $message->setUser($user);
        $message->setText($text);
        $message->setParent($parent);
        return $message;
    }
}
