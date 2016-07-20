<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:39
 */

namespace FusionClone\FacturasBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ExecutionContext;

/**
 * @ORM\Entity(repositoryClass="FusionClone\FacturasBundle\Entity\FacturaRepository")
 * @ORM\Table(name="facturas")
 */

class Factura
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=100, nullable=true)
     */
    private $codigo;

    /**
     * @var date
     *
     * @ORM\Column(name="fecha", type="date", nullable=true)
     */
    private $fecha;

    /**
     * @var date
     *
     * @ORM\Column(name="fechaVenc", type="date", nullable=true)
     */
    private $fechaVenc;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomMone")
     */
    private $moneda;

    /**
     * @var float
     *
     * @ORM\Column(name="tasa", type="decimal", nullable=true, precision=10, scale=7)
     */
    private $tasa;

    /**
     * @var float
     *
     * @ORM\Column(name="importe", type="decimal", nullable=true, precision=10, scale=2)
     */
    private $importe;

    /**
     * @var float
     *
     * @ORM\Column(name="saldo", type="decimal", nullable=true, precision=10, scale=2)
     */
    private $saldo;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomEsta")
     */
    private $estado;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\ClientesBundle\Entity\Cliente")
     */
    private $cliente;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomTdocConf")
     */
    private $tdocConf;

    /**
     * @var string
     *
     * @ORM\Column(name="terms", type="text", nullable=true)
     */
    private $terms;

    /**
     * @var string
     *
     * @ORM\Column(name="pie", type="text", nullable=true)
     */
    private $pie;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\UsuariosBundle\Entity\Usuario")
     */
    private $usuario;

    private $factItems;

    private $factImps;


    public function __construct()
    {
        $this->recurrencia = new ArrayCollection();
        $this->factItems = new ArrayCollection();
        $this->factImps = new ArrayCollection();
    }

    public function getFactItems()
    {
        return $this->factItems;
    }

    public function addFactItem(FacturaItem $factItem)
    {
        if (is_null($this->factItems)) {
            $this->factItems = new ArrayCollection();
        }
        $factItem->setFactura($this);
        $this->factItems->add($factItem);
    }

    public function removeFactItem(FacturaItem $factItem)
    {
        $this->factItems->removeElement($factItem);
    }

    public function getFactImps()
    {
        return $this->factImps;
    }

    public function addFactImp(FacturaImp $factImp)
    {
        if (is_null($this->factImps)) {
            $this->factImps = new ArrayCollection();
        }
        $factImp->setFactura($this);
        $this->factImps->add($factImp);
    }

    public function removeFactImp(FacturaImp $factImp)
    {
        $this->factImps->removeElement($factImp);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $cliente
     */
    public function setCliente($cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * @return int
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * @param string $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param int $estado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    /**
     * @return int
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param datetime $fechaVenc
     */
    public function setFechaVenc($fechaVenc)
    {
        $this->fechaVenc = $fechaVenc;
    }

    /**
     * @return \FusionClone\ClientesBundle\Entity\date
     */
    public function getFechaVenc()
    {
        return $this->fechaVenc;
    }

    /**
     * @param \FusionClone\ClientesBundle\Entity\date $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return \FusionClone\ClientesBundle\Entity\date
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param int $moneda
     */
    public function setMoneda($moneda)
    {
        $this->moneda = $moneda;
    }

    /**
     * @return int
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * @param float $tasa
     */
    public function setTasa($tasa)
    {
        $this->tasa = $tasa;
    }

    /**
     * @return float
     */
    public function getTasa()
    {
        return $this->tasa;
    }


    /**
     * @param float $importe
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;
    }

    /**
     * @return float
     */
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * @param float $saldo
     */
    public function setSaldo($saldo)
    {
        $this->saldo = $saldo;
    }

    /**
     * @return float
     */
    public function getSaldo()
    {
        return $this->saldo;
    }

    /**
     * @param int $tdocConf
     */
    public function setTdocConf($tdocConf)
    {
        $this->tdocConf = $tdocConf;
    }

    /**
     * @return int
     */
    public function getTdocConf()
    {
        return $this->tdocConf;
    }

    /**
     * @param string $terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

    /**
     * @return string
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param string $pie
     */
    public function setPie($pie)
    {
        $this->pie = $pie;
    }

    /**
     * @return string
     */
    public function getPie()
    {
        return $this->pie;
    }

    /**
     * @param int $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return int
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    public function __toString()
    {
        return $this->getCodigo();
    }
} 