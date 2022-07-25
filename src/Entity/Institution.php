<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\InstitutionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Traits\TimestampableEntity;

/**
 * @ApiResource(
 *  collectionOperations={"get"},
 *  itemOperations={"get"},
 *  normalizationContext={"groups"={"get"}}
 * )
 * @ORM\Entity(repositoryClass=InstitutionRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"name"})})
 * @Gedmo\Loggable
 */
class Institution
{
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
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $schedule;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $daysOpen;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $webpage;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookURL;

    /**
     * @Groups("get")
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $twitterURL;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="institutions", fetch="EXTRA_LAZY")
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getDaysOpen(): ?string
    {
        return $this->daysOpen;
    }

    public function setDaysOpen(string $daysOpen): self
    {
        $this->daysOpen = $daysOpen;

        return $this;
    }

    public function getWebpage(): ?string
    {
        return $this->webpage;
    }

    public function setWebpage(string $webpage): self
    {
        $this->webpage = $webpage;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFacebookURL(): ?string
    {
        return $this->facebookURL;
    }

    public function setFacebookURL(string $facebookURL): self
    {
        $this->facebookURL = $facebookURL;

        return $this;
    }

    public function getTwitterURL(): ?string
    {
        return $this->twitterURL;
    }

    public function setTwitterURL(string $twitterURL): self
    {
        $this->twitterURL = $twitterURL;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

}
