<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CartRepository")
 */
class Cart
{

    const MODE_CASH = 0;
    const MODE_BANK_CARD = 1;
    const MODE_PAYPAL = 2;

    const PAYMENT_MODES = [
        self::MODE_CASH => [
            'icon' => 'icon-money',
            'label' => 'Chèque ou espèces',
        ],
        self::MODE_BANK_CARD => [
            'icon' => 'icon-credit-cards',
            'label' => 'Carte bancaire',
        ],
        self::MODE_PAYPAL => [
            'icon' => 'icon-paypal',
            'label' => 'Paypal',
        ],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="carts")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="carts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="id_cookie", type="string", nullable=false, unique=true)
     */
    private $idCookie;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="appointment", type="datetime", nullable=true)
     */
    private $appointment;

    /**
     * @var bool
     *
     * @ORM\Column(name="remote", type="boolean")
     */
    private $remote;

    /**
     * @var string
     *
     * @ORM\Column(name="payment", type="string", length=255, nullable=true))
     */
    private $payment;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid = false;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set appointment
     *
     * @param \DateTime $appointment
     *
     * @return Cart
     */
    public function setAppointment($appointment)
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdCookie()
    {
        return $this->idCookie;
    }

    /**
     * @param $idCookie
     *
     * @return $this
     */
    public function setIdCookie($idCookie)
    {
        $this->idCookie = $idCookie;

        return $this;
    }

    /**
     * Get appointment
     *
     * @return \DateTime
     */
    public function getAppointment()
    {

        return $this->appointment;
    }

    /**
     * @return bool
     */
    public function isRemote()
    {
        return $this->remote;
    }

    /**
     * @param $remote
     *
     * @return $this
     */
    public function setRemote($remote)
    {
        $this->remote = $remote;

        return $this;
    }

    /**
     * Set payment
     *
     * @param string $payment
     *
     * @return Cart
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return string
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Cart
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }
    
    public function getDateRange()
    {
        $date = $this->getAppointment();
    }
}

