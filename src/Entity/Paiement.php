<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaiementRepository::class)
 */
class Paiement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=order::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $orders;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $paiementStatus;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $method;

    /**
     * @ORM\Column(type="integer")
     */
    private $paiementApi;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrders(): ?order
    {
        return $this->orders;
    }

    public function setOrders(order $orders): self
    {
        $this->orders = $orders;

        return $this;
    }

    public function getPaiementStatus(): ?string
    {
        return $this->paiementStatus;
    }

    public function setPaiementStatus(string $paiementStatus): self
    {
        $this->paiementStatus = $paiementStatus;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getPaiementApi(): ?int
    {
        return $this->paiementApi;
    }

    public function setPaiementApi(int $paiementApi): self
    {
        $this->paiementApi = $paiementApi;

        return $this;
    }
}
