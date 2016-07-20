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
 * @ORM\Entity(repositoryClass="FusionClone\FacturasBundle\Entity\FacturaItemRepository")
 * @ORM\Table(name="facturaitem")
 */
class FacturaItem
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

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomProd", cascade={"persist"})
     */
    protected $producto;

    /** @ORM\Column(name="descripcion", type="string") */
    protected $descripcion;

    /** @ORM\Column(name="precio", type="decimal", precision=10, scale=2, nullable=false) */
    protected $precio;

    /** @ORM\Column(name="cantidad", type="decimal", precision=10, scale=2, nullable=false) */
    protected $cantidad;

    /** @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomImp") */
    protected $impuesto;

    /**
     * @var integer
     *
     */
    protected $total;

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
     * @param mixed $cantidad
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    }

    /**
     * @return mixed
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param int $producto
     */
    public function setProducto($producto)
    {
        $this->producto = $producto;
    }

    /**
     * @return int
     */
    public function getProducto()
    {
        return $this->producto;
    }

    /**
     * @param mixed $precio
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    /**
     * @return mixed
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
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


    public function __toString()
    {
        return $this->getNombre();
    }

} 