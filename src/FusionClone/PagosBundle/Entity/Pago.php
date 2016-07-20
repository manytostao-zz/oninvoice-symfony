<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 20/09/14
 * Time: 22:38
 */

namespace FusionClone\PagosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FusionClone\PagosBundle\Entity\PagoRepository")
 * @ORM\Table(name="pagos")
 */
class Pago
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

    /** @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomPagos") */
    protected $metodo;

    /** @ORM\Column(name="fecha", type="date") */
    protected $fecha;

    /** @ORM\Column(name="nota", type="string", length=255) */
    protected $nota;

    /** @ORM\Column(name="importe", type="decimal", length=255, precision=10, scale=2) */
    protected $importe;

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
     * @param mixed $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
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
     * @param mixed $metodo
     */
    public function setMetodo($metodo)
    {
        $this->metodo = $metodo;
    }

    /**
     * @return mixed
     */
    public function getMetodo()
    {
        return $this->metodo;
    }

    /**
     * @param mixed $nota
     */
    public function setNota($nota)
    {
        $this->nota = $nota;
    }

    /**
     * @return mixed
     */
    public function getNota()
    {
        return $this->nota;
    }

    /**
     * @param mixed $importe
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;
    }

    /**
     * @return mixed
     */
    public function getImporte()
    {
        return $this->importe;
    }



    public function __toString()
    {
        return !is_null($this->getImpuesto()) ? $this->getImpuesto() : '';
    }

} 