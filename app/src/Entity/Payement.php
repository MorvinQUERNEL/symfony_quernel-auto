<?php

namespace App\Entity;

use App\Repository\PayementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PayementRepository::class)]
class Payement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $payementAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $payementStatus = null;

    #[ORM\ManyToOne(inversedBy: 'payement')]
    private ?Orders $orders = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPayementAt(): ?\DateTimeImmutable
    {
        return $this->payementAt;
    }

    public function setPayementAt(\DateTimeImmutable $payementAt): static
    {
        $this->payementAt = $payementAt;

        return $this;
    }

    public function getPayementStatus(): ?string
    {
        return $this->payementStatus;
    }

    public function setPayementStatus(?string $payementStatus): static
    {
        $this->payementStatus = $payementStatus;

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
