<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 50)]
    private ?string $orderStatus = null;

    #[ORM\Column(type: 'bigint')]
    private ?int $totalPrice = null;

    #[ORM\Column(length: 100)]
    private ?string $deliveryCity = null;

    #[ORM\Column]
    private ?int $deliveryPostalCode = null;

    #[ORM\Column(length: 50)]
    private ?string $deliveryCountry = null;

    #[ORM\Column(length: 100)]
    private ?string $deliveryAdress = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Users $users = null;

    /**
     * @var Collection<int, Payement>
     */
    #[ORM\OneToMany(targetEntity: Payement::class, mappedBy: 'orders')]
    private Collection $payement;

    /**
     * @var Collection<int, Vehicules>
     */
    #[ORM\OneToMany(targetEntity: Vehicules::class, mappedBy: 'orders')]
    private Collection $vehicules;

    public function __construct()
    {
        $this->payement = new ArrayCollection();
        $this->vehicules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(string $orderStatus): static
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(string $deliveryCity): static
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryPostalCode(): ?int
    {
        return $this->deliveryPostalCode;
    }

    public function setDeliveryPostalCode(int $deliveryPostalCode): static
    {
        $this->deliveryPostalCode = $deliveryPostalCode;

        return $this;
    }

    public function getDeliveryCountry(): ?string
    {
        return $this->deliveryCountry;
    }

    public function setDeliveryCountry(string $deliveryCountry): static
    {
        $this->deliveryCountry = $deliveryCountry;

        return $this;
    }

    public function getDeliveryAdress(): ?string
    {
        return $this->deliveryAdress;
    }

    public function setDeliveryAdress(string $deliveryAdress): static
    {
        $this->deliveryAdress = $deliveryAdress;

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

    /**
     * @return Collection<int, Payement>
     */
    public function getPayement(): Collection
    {
        return $this->payement;
    }

    public function addPayement(Payement $payement): static
    {
        if (!$this->payement->contains($payement)) {
            $this->payement->add($payement);
            $payement->setOrders($this);
        }

        return $this;
    }

    public function removePayement(Payement $payement): static
    {
        if ($this->payement->removeElement($payement)) {
            // set the owning side to null (unless already changed)
            if ($payement->getOrders() === $this) {
                $payement->setOrders(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vehicules>
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicules $vehicule): static
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules->add($vehicule);
            $vehicule->setOrders($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicules $vehicule): static
    {
        if ($this->vehicules->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getOrders() === $this) {
                $vehicule->setOrders(null);
            }
        }

        return $this;
    }
}
