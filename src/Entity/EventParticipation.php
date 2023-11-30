<?php

namespace App\Entity;

use App\Repository\EventParticipationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventParticipationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EventParticipation
{
    use Traits\TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'eventParticipations')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullname = null;

    #[ORM\ManyToOne(inversedBy: 'eventParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?bool $banned = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): static
    {
        $this->banned = $banned;

        return $this;
    }
}
