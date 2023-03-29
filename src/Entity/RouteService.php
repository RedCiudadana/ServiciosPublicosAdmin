<?php

namespace App\Entity;

use App\Repository\RouteServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RouteServiceRepository::class)
 */
class RouteService
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=RouteServiceItem::class, mappedBy="routeService", orphanRemoval=true, cascade={"persist"})
     */
    private $routeServiceItems;

    public function __construct()
    {
        $this->routeServiceItems = new ArrayCollection();
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, RouteServiceItem>
     */
    public function getRouteServiceItems(): Collection
    {
        return $this->routeServiceItems;
    }

    public function addRouteServiceItem(RouteServiceItem $routeServiceItem): self
    {
        if (!$this->routeServiceItems->contains($routeServiceItem)) {
            $this->routeServiceItems[] = $routeServiceItem;
            $routeServiceItem->setRouteService($this);
        }

        return $this;
    }

    public function removeRouteServiceItem(RouteServiceItem $routeServiceItem): self
    {
        if ($this->routeServiceItems->removeElement($routeServiceItem)) {
            // set the owning side to null (unless already changed)
            if ($routeServiceItem->getRouteService() === $this) {
                $routeServiceItem->setRouteService(null);
            }
        }

        return $this;
    }
}
