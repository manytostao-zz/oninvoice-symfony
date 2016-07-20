<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 20/09/14
 * Time: 22:38
 */

namespace FusionClone\CotizacionesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cotimp")
 */
class CotImp
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
     * @ORM\ManyToOne(targetEntity="FusionClone\CotizacionesBundle\Entity\Cotizacion")
     */
    protected $cotizacion;

    /** @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomImp") */
    protected $impuesto;

    /** @ORM\Column(name="antesImpItem", type="boolean") */
    protected $antesImpItem;

    /**
     * @var float
     *
     */
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
     * @param int $cotizacion
     */
    public function setCotizacion($cotizacion)
    {
        $this->cotizacion = $cotizacion;
    }

    /**
     * @return int
     */
    public function getCotizacion()
    {
        return $this->cotizacion;
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