<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=client::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=product::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;


    /**
     * @ORM\OneToOne(targetEntity=client::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $facturationAdress;

    /**
     * @ORM\OneToOne(targetEntity=deliveryAdress::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $deliveryAdress;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?client
    {
        return $this->client;
    }

    public function setClient(client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getProduct(): ?product
    {
        return $this->product;
    }

    public function setProduct(?product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getFacturationAdress(): ?client
    {
        return $this->facturationAdress;
    }

    public function setFacturationAdress(client $facturationAdress): self
    {
        $this->facturationAdress = $facturationAdress;
        return $this;
    }

    public function getDeliveryAdress(): ?deliveryAdress
    {
        return $this->deliveryAdress;
    }

    public function setDeliveryAdress(?deliveryAdress $deliveryAdress): self
    {
        $this->deliveryAdress = $deliveryAdress;

        return $this;
    }
}
