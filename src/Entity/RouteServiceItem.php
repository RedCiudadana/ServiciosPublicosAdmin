<?php

namespace App\Entity;

use App\Repository\RouteServiceItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RouteServiceItemRepository::class)
 */
class RouteServiceItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PublicService::class, inversedBy="routeServiceItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $publicService;

    /**
     * @ORM\ManyToOne(targetEntity=RouteService::class, inversedBy="routeServiceItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $routeService;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicService(): ?PublicService
    {
        return $this->publicService;
    }

    public function setPublicService(?PublicService $publicService): self
    {
        $this->publicService = $publicService;

        return $this;
    }

    public function getRouteService(): ?RouteService
    {
        return $this->routeService;
    }

    public function setRouteService(?RouteService $routeService): self
    {
        $this->routeService = $routeService;

        return $this;
    }
}
