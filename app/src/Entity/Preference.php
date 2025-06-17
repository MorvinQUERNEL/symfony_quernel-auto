<?php

namespace App\Entity;

use App\Repository\PreferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreferenceRepository::class)]
class Preference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prefereneceBrand = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $preferenceModel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $minYear = null;

    #[ORM\Column]
    private ?int $maxPrice = null;

    #[ORM\Column(length: 30)]
    private ?string $preferenceFuelType = null;

    #[ORM\ManyToOne(inversedBy: 'preferences')]
    private ?Users $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrefereneceBrand(): ?string
    {
        return $this->prefereneceBrand;
    }

    public function setPrefereneceBrand(?string $preferenece_brand): static
    {
        $this->prefereneceBrand = $preferenece_brand;

        return $this;
    }

    public function getPreferenceModel(): ?string
    {
        return $this->preferenceModel;
    }

    public function setPreferenceModel(?string $preference_model): static
    {
        $this->preferenceModel = $preference_model;

        return $this;
    }

    public function getMinYear(): ?\DateTime
    {
        return $this->minYear;
    }

    public function setMinYear(?\DateTime $minYear): static
    {
        $this->minYear = $minYear;

        return $this;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(int $maxPrice): static
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function getPreferenceFuelType(): ?string
    {
        return $this->preferenceFuelType;
    }

    public function setPreferenceFuelType(string $preferenceFuelType): static
    {
        $this->preferenceFuelType = $preferenceFuelType;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;

        return $this;
    }
}
