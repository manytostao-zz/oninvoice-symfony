<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:39
 */

namespace FusionClone\ClientesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
 * @ORM\Entity(repositoryClass="FusionClone\ClientesBundle\Entity\ClienteRepository")
 * @ORM\Table(name="clientes")
 * @Assert\Callback(methods={"nameNotNull"})
 */

class Cliente
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
     * @ORM\Column(name="nombre", type="string", length=100, nullable=true)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="text", nullable=true)
     */
    private $direccion;

    /**
     * @var float
     *
     * @ORM\Column(name="telefono", type="decimal", nullable=true)
     */
    private $telefono;

    /**
     * @var float
     *
     * @ORM\Column(name="fax", type="decimal", nullable=true)
     */
    private $fax;

    /**
     * @var float
     *
     * @ORM\Column(name="movil", type="decimal", nullable=true)
     */
    private $movil;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="webpage", type="string", length=255, nullable=true)
     */
    private $webpage;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomMone")
     */
    private $defMone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @var float
     */
    private $saldo;

    /**
     * @var float
     */
    private $facturado;

    /**
     * @var float
     */
    private $pagado;

    /**
     * @param boolean $activo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    /**
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param int $defMone
     */
    public function setDefMone($defMone)
    {
        $this->defMone = $defMone;
    }

    /**
     * @return int
     */
    public function getDefMone()
    {
        return $this->defMone;
    }

    /**
     * @param \FusionClone\ClientesBundle\Entity\text $direccion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }

    /**
     * @return \FusionClone\ClientesBundle\Entity\text
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param float $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return float
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param float $movil
     */
    public function setMovil($movil)
    {
        $this->movil = $movil;
    }

    /**
     * @return float
     */
    public function getMovil()
    {
        return $this->movil;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param float $telefono
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    /**
     * @return float
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param string $webpage
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;
    }

    /**
     * @return string
     */
    public function getWebpage()
    {
        return $this->webpage;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $saldo
     */
    public function setSaldo($saldo)
    {
        $this->saldo = $saldo;
    }

    /**
     * @return mixed
     */
    public function getSaldo()
    {
        return $this->saldo;
    }

    /**
     * @param float $facturado
     */
    public function setFacturado($facturado)
    {
        $this->facturado = $facturado;
    }

    /**
     * @return float
     */
    public function getFacturado()
    {
        return $this->facturado;
    }

    /**
     * @param float $pagado
     */
    public function setPagado($pagado)
    {
        $this->pagado = $pagado;
    }

    /**
     * @return float
     */
    public function getPagado()
    {
        return $this->pagado;
    }



    public function __toString()
    {
        return $this->getNombre();
    }

    public function nameNotNull(ExecutionContext $context)
    {
        $nombre = $this->getNombre();

        if (is_null($nombre)) {

            $context->addViolation(
                'El campo nombre no puede estar vacío',
                array(),
                null
            );

            return;

        }
    }
} 