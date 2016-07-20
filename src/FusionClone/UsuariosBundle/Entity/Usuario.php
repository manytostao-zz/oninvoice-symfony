<?php

namespace FusionClone\UsuariosBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * FusionClone\UsuariosBundle\Entity\Usuario
 *
 * @ORM\Entity(repositoryClass="FusionClone\UsuariosBundle\Entity\UsuarioRepository")
 * @ORM\Table(name="usuario")
 */
class Usuario implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $fechaAlta;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $compannia;

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
     * @ORM\Column(name="webpage", type="string", length=255, nullable=true)
     */
    private $webpage;

    /**
     * @var integer
     *
     * @ORM\Column(name="cliente_id", type="integer", nullable=true)
     */
    private $cliente;

    /**
     * @var integer
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    public function __construct()
    {
        $this->fechaAlta = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Usuario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
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
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Usuario
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Usuario
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set fechaAlta
     *
     * @param \DateTime $fechaAlta
     * @return Usuario
     */
    public function setFechaAlta($fechaAlta)
    {
        $this->fechaAlta = $fechaAlta;

        return $this;
    }

    /**
     * Get fechaAlta
     *
     * @return \DateTime
     */
    public function getFechaAlta()
    {
        return $this->fechaAlta;
    }

    /**
     * @param string $compañia
     */
    public function setCompannia($compañia)
    {
        $this->compannia = $compañia;
    }

    /**
     * @return string
     */
    public function getCompannia()
    {
        return $this->compannia;
    }

    /**
     * @param \FusionClone\UsuariosBundle\Entity\text $direccion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }

    /**
     * @return \FusionClone\UsuariosBundle\Entity\text
     */
    public function getDireccion()
    {
        return $this->direccion;
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
     * @param int $activo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    /**
     * @return int
     */
    public function getActivo()
    {
        return $this->activo;
    }


    //From UserInterface
    function eraseCredentials()
    {
    }

    function getRoles()
    {
        if (!is_null($this->getCliente())) {
            return array('ROLE_CLIENTE');
        }

        return array('ROLE_USUARIO');
    }
}
