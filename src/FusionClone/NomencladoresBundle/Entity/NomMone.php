<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:32
 */

namespace FusionClone\NomencladoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FusionClone\NomencladoresBundle\Entity\NomMoneRepository")
 * @ORM\Table(name="nommone")
 */
class NomMone {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** @ORM\Column(type="string", length=5) */
    protected $codigo;

    /** @ORM\Column(type="string", length=100) */
    protected $descripcion;

    /** @ORM\Column(type="string", length=5) */
    protected $simbolo;

    /** @ORM\Column(name="ubicaSimbol", type="boolean") */
    protected $ubicaSimbol;

    /** @ORM\Column(type="string", length=1) */
    protected $signDecimal;

    /** @ORM\Column(type="string", length=1) */
    protected $signMillares;

    /** @ORM\Column(type="decimal", precision=10, scale=7) */
    protected $tasa;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->codigo;
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
     * @param mixed $signDecimal
     */
    public function setSignDecimal($signDecimal)
    {
        $this->signDecimal = $signDecimal;
    }

    /**
     * @return mixed
     */
    public function getSignDecimal()
    {
        return $this->signDecimal;
    }

    /**
     * @param mixed $signMillares
     */
    public function setSignMillares($signMillares)
    {
        $this->signMillares = $signMillares;
    }

    /**
     * @return mixed
     */
    public function getSignMillares()
    {
        return $this->signMillares;
    }

    /**
     * @param mixed $simbolo
     */
    public function setSimbolo($simbolo)
    {
        $this->simbolo = $simbolo;
    }

    /**
     * @return mixed
     */
    public function getSimbolo()
    {
        return $this->simbolo;
    }

    /**
     * @param mixed $ubicaSimbol
     */
    public function setUbicaSimbol($ubicaSimbol)
    {
        $this->ubicaSimbol = $ubicaSimbol;
    }

    /**
     * @return mixed
     */
    public function getUbicaSimbol()
    {
        return $this->ubicaSimbol;
    }

    /**
     * @param mixed $tasa
     */
    public function setTasa($tasa)
    {
        $this->tasa = $tasa;
    }

    /**
     * @return mixed
     */
    public function getTasa()
    {
        return $this->tasa;
    }

    public function __toString(){
        return $this->getDescripcion();
    }
} 