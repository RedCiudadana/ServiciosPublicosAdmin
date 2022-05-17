<?php

namespace App\Entity;

use App\Repository\PublicServiceRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *  normalizationContext={"groups"={"get"}},
 *  collectionOperations={"get"},
 *  itemOperations={"get"},
 * )
 * @ORM\Entity(repositoryClass=PublicServiceRepository::class)
 */
class PublicService
{
    /**
     * @Groups("get")
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
     * @Groups("get")
     * @ORM\ManyToOne(targetEntity=Institution::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $institution;

    /**
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $instructions;

    /**
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $requirements;

    /**
     * @Groups("get")
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @Groups("get")
     * @ORM\Column(type="string")
     */
    private $timeResponse;

    /**
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $typeOfDocumentObtainable;

    /**
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @Groups("get")
     * @ORM\ManyToOne(targetEntity=SubCategory::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $subcategory;

    /**
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $normative;

    /**
     * @Groups("get")
     * @ORM\Column(type="boolean")
     */
    private $highlight;

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

    public function getTimeResponse(): ?string
    {
        return $this->timeResponse;
    }

    public function setTimeResponse(string $timeResponse): self
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

    public function getSubcategory(): ?SubCategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?SubCategory $subcategory): self
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    public function getNormative(): ?string
    {
        return $this->normative;
    }

    public function setNormative(string $normative): self
    {
        $this->normative = $normative;

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
}
