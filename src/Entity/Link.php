<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 */
class Link
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $url = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $shortUrl = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title = '';

    /**
     * @ORM\Column(type="simple_array", nullable=false)
     */
    private $tags = [];

    /**
     * @ORM\OneToMany(targetEntity=LinkVisit::class, mappedBy="link", orphanRemoval=true)
     */
    private $linkVisits;

    public function __construct()
    {
        $this->linkVisits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    public function setShortUrl(string $shortUrl): self
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTags(): ?array
    {
        return array_filter($this->tags);
    }

    public function setTags(array $tags): self
    {
        $this->tags = array_filter($tags) ?: [null];

        return $this;
    }

    /**
     * @return Collection|LinkVisit[]
     */
    public function getLinkVisits(): Collection
    {
        return $this->linkVisits;
    }

    public function addLinkVisit(LinkVisit $linkVisit): self
    {
        if (!$this->linkVisits->contains($linkVisit)) {
            $this->linkVisits[] = $linkVisit;
            $linkVisit->setLink($this);
        }

        return $this;
    }

    public function removeLinkVisit(LinkVisit $linkVisit): self
    {
        if ($this->linkVisits->removeElement($linkVisit)) {
            // set the owning side to null (unless already changed)
            if ($linkVisit->getLink() === $this) {
                $linkVisit->setLink(null);
            }
        }

        return $this;
    }
}
