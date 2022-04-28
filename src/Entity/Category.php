<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=PublicService::class, mappedBy="category")
     */
    private $publicServices;

    public function __construct()
    {
        $this->publicServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, PublicService>
     */
    public function getPublicServices(): Collection
    {
        return $this->publicServices;
    }

    public function addPublicService(PublicService $publicService): self
    {
        if (!$this->publicServices->contains($publicService)) {
            $this->publicServices[] = $publicService;
            $publicService->setCategory($this);
        }

        return $this;
    }

    public function removePublicService(PublicService $publicService): self
    {
        if ($this->publicServices->removeElement($publicService)) {
            // set the owning side to null (unless already changed)
            if ($publicService->getCategory() === $this) {
                $publicService->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
