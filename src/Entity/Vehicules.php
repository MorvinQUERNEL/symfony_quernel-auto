<?php

namespace App\Entity;

use App\Repository\VehiculesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: VehiculesRepository::class)]
class Vehicules
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read'])]
    private ?string $model = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read'])]
    private ?\DateTime $year = null;

    #[ORM\Column]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read', 'order:list'])]
    private ?int $mileage = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read', 'order:list'])]
    private ?string $fuelType = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read', 'order:list'])]
    private ?string $trasmission = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicule:read'])]
    private ?string $color = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['vehicule:read'])]
    private ?int $doorCount = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['vehicule:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['vehicule:read', 'vehicule:list'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read'])]
    private ?int $salePrice = null;

    /**
     * @var Collection<int, Pictures>
     */
    #[ORM\OneToMany(targetEntity: Pictures::class, mappedBy: 'vehicules', cascade: ['persist', 'remove'])]
    #[Groups(['vehicule:read', 'vehicule:list', 'order:read', 'order:list'])]
    private Collection $pictures;

    #[ORM\ManyToOne(inversedBy: 'vehicules')]
    private ?Orders $orders = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?\DateTime
    {
        return $this->year;
    }

    public function setYear(\DateTime $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function setFuelType(string $fuelType): static
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    public function getTrasmission(): ?string
    {
        return $this->trasmission;
    }

    public function setTrasmission(string $trasmission): static
    {
        $this->trasmission = $trasmission;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getDoorCount(): ?int
    {
        return $this->doorCount;
    }

    public function setDoorCount(int $doorCount): static
    {
        $this->doorCount = $doorCount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    public function setSalePrice(int $salePrice): static
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    /**
     * @return Collection<int, Pictures>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Pictures $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setVehicules($this);
        }

        return $this;
    }

    public function removePicture(Pictures $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getVehicules() === $this) {
                $picture->setVehicules(null);
            }
        }

        return $this;
    }

    public function getOrders(): ?Orders
    {
        return $this->orders;
    }

    public function setOrders(?Orders $orders): static
    {
        $this->orders = $orders;

        return $this;
    }
}
