<?php

namespace App\Entity;

use App\Repository\InstitutionRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  collectionOperations={"get"},
 *  itemOperations={"get"},
 * )
 * @ORM\Entity(repositoryClass=InstitutionRepository::class)
 * @Gedmo\Loggable
 */
class Institution
{
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
     * @Gedmo\Versioned
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $schedule;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $daysOpen;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $webpage;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $facebookURL;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
     */
    private $twitterURL;

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
}
