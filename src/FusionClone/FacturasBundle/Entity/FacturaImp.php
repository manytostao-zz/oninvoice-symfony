<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 20/09/14
 * Time: 22:38
 */

namespace FusionClone\FacturasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="facturaimp")
 */
class FacturaImp
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\FacturasBundle\Entity\Factura")
     */
    protected $factura;

    /** @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomImp") */
    protected $impuesto;

    /** @ORM\Column(name="antesImpItem", type="boolean") */
    protected $antesImpItem;

    /** @ORM\Column(name="total", type="decimal", precision=10, scale=3) */
    protected $total;

    /**
     * @var float
     *
     */
    protected $porcentaje;

    /**
     * @param mixed $antesImpItem
     */
    public function setAntesImpItem($antesImpItem)
    {
        $this->antesImpItem = $antesImpItem;
    }

    /**
     * @return mixed
     */
    public function getAntesImpItem()
    {
        return $this->antesImpItem;
    }

    /**
     * @param int $factura
     */
    public function setFactura($factura)
    {
        $this->factura = $factura;
    }

    /**
     * @return int
     */
    public function getFactura()
    {
        return $this->factura;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $impuesto
     */
    public function setImpuesto($impuesto)
    {
        $this->impuesto = $impuesto;
    }

    /**
     * @return mixed
     */
    public function getImpuesto()
    {
        return $this->impuesto;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $porcentaje
     */
    public function setPorcentaje($porcentaje)
    {
        $this->porcentaje = $porcentaje;
    }

    /**
     * @return float
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }

    public function __toString()
    {
        return !is_null($this->getImpuesto()) ? $this->getImpuesto() : '';
    }

} 