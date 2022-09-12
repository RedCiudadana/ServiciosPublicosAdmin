<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 */
class Currency
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups("get")
     * @ORM\Column(type="string", length=255)
     */
    private $symbol;

    /**
     * @Groups("get")
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $code;

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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCodeAndSymbol(): string
    {
        return sprintf('%s - %s', $this->code, $this->symbol);
    }
}
