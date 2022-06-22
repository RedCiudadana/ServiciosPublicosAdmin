<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PublicServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  normalizationContext={"groups"={"get"}},
 *  collectionOperations={"get"},
 *  itemOperations={"get"},
 * )
 * @ORM\Entity(repositoryClass=PublicServiceRepository::class)
 * @Gedmo\Loggable
 */
class PublicService
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    use TimestampableEntity;
    use BlameableEntity;

    /**
     * @Groups("get")
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255, options={"default" : self::STATUS_DRAFT})
     */
    private $status = self::STATUS_DRAFT;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\ManyToOne(targetEntity=Institution::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $institution;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $institutionDepartment;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $instructions;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="text")
     */
    private $requirements;

    /**
     * @Gedmo\Versioned
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
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $typeOfDocumentObtainable;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\ManyToOne(targetEntity=SubCategory::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $subcategory;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $normative;

    /**
     * @Gedmo\Versioned
     * @Groups("get")
     * @ORM\Column(type="boolean", options={"default" : false}, nullable=true)
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

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of institutionDepartment
     */ 
    public function getInstitutionDepartment()
    {
        return $this->institutionDepartment;
    }

    /**
     * Set the value of institutionDepartment
     *
     * @return  self
     */ 
    public function setInstitutionDepartment($institutionDepartment)
    {
        $this->institutionDepartment = $institutionDepartment;

        return $this;
    }
}
