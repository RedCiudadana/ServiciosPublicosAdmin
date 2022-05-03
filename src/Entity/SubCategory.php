<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubCategoryRepository::class)
 */
class SubCategory
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="subCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $highlight;

    /**
     * @ORM\OneToMany(targetEntity=PublicService::class, mappedBy="subcategory")
     */
    private $publicServices;

    public function __construct()
    {
        $this->publicServices = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getHighlight(): ?bool
    {
        return $this->highlight;
    }

    public function setHighlight(bool $highlight): self
    {
        $this->highlight = $highlight;

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
            $publicService->setSubcategory($this);
        }

        return $this;
    }

    public function removePublicService(PublicService $publicService): self
    {
        if ($this->publicServices->removeElement($publicService)) {
            // set the owning side to null (unless already changed)
            if ($publicService->getSubcategory() === $this) {
                $publicService->setSubcategory(null);
            }
        }

        return $this;
    }
}
