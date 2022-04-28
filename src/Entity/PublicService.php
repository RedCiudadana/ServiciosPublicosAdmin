<?php

namespace App\Entity;

use App\Repository\PublicServiceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublicServiceRepository::class)
 */
class PublicService
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
     * @ORM\ManyToOne(targetEntity=Institution::class, inversedBy="publicServices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="publicServices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     */
    private $instructions;

    /**
     * @ORM\Column(type="text")
     */
    private $requirements;

    /**
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @ORM\Column(type="dateinterval")
     */
    private $timeResponse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $typeOfDocumentObtainable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getRequirements(): ?string
    {
        return $this->requirements;
    }

    public function setRequirements(string $requirements): self
    {
        $this->requirements = $requirements;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getTimeResponse(): ?\DateInterval
    {
        return $this->timeResponse;
    }

    public function setTimeResponse(\DateInterval $timeResponse): self
    {
        $this->timeResponse = $timeResponse;

        return $this;
    }

    public function getTypeOfDocumentObtainable(): ?string
    {
        return $this->typeOfDocumentObtainable;
    }

    public function setTypeOfDocumentObtainable(string $typeOfDocumentObtainable): self
    {
        $this->typeOfDocumentObtainable = $typeOfDocumentObtainable;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
