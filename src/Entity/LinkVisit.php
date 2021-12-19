<?php

namespace App\Entity;

use App\Repository\LinkVisitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinkVisitRepository::class)
 */
class LinkVisit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private string $ip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $userAgent;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $date;

    /**
     * @ORM\ManyToOne(targetEntity=Link::class, inversedBy="linkVisits")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Link $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function setLink(?Link $link): self
    {
        $this->link = $link;

        return $this;
    }
}
