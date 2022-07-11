<?php

namespace App\Entity;

use App\Repository\PublicServiceEvaluationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublicServiceEvaluationRepository::class)
 */
class PublicServiceEvaluation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PublicService::class)
     */
    private $publicService;

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
}
