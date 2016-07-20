<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 13/10/14
 * Time: 14:23
 */

namespace FusionClone\FacturasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="facturarec")
 */
class FacturaRec
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
     * @ORM\Column(name="facBase", type="integer", nullable=true)
     */
    private $facBase;

    /**
     * @var integer
     *
     * @ORM\Column(name="cada", type="integer", nullable=true)
     */
    private $cada;

    /**
     * @var boolean
     *
     * @ORM\Column(name="anno", type="boolean", nullable=true)
     */
    private $anno;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mes", type="boolean", nullable=true)
     */
    private $mes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="semana", type="boolean", nullable=true)
     */
    private $semana;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dia", type="boolean", nullable=true)
     */
    private $dia;

    /**
     * @var date
     *
     * @ORM\Column(name="fechaIni", type="date", nullable=true)
     */
    private $fechaIni;

    /**
     * @var date
     *
     * @ORM\Column(name="fechaFin", type="date", nullable=true)
     */
    private $fechaFin;

    /**
     * @var date
     *
     * @ORM\Column(name="proxFecha", type="date", nullable=true)
     */
    private $proxFecha;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activo", type="boolean", nullable=true)
     */
    private $activo;

    /**
     * @return mixed
     */

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $anno
     */
    public function setAnno($anno)
    {
        $this->anno = $anno;
    }

    /**
     * @return boolean
     */
    public function getAnno()
    {
        return $this->anno;
    }

    /**
     * @param int $cada
     */
    public function setCada($cada)
    {
        $this->cada = $cada;
    }

    /**
     * @return int
     */
    public function getCada()
    {
        return $this->cada;
    }

    /**
     * @param boolean $dia
     */
    public function setDia($dia)
    {
        $this->dia = $dia;
    }

    /**
     * @return boolean
     */
    public function getDia()
    {
        return $this->dia;
    }

    /**
     * @param int $facBase
     */
    public function setFacBase($facBase)
    {
        $this->facBase = $facBase;
    }

    /**
     * @return int
     */
    public function getFacBase()
    {
        return $this->facBase;
    }

    /**
     * @param \FusionClone\FacturasBundle\Entity\date $fechaFin
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    }

    /**
     * @return \FusionClone\FacturasBundle\Entity\date
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * @param \FusionClone\FacturasBundle\Entity\date $fechaIni
     */
    public function setFechaIni($fechaIni)
    {
        $this->fechaIni = $fechaIni;
    }

    /**
     * @return \FusionClone\FacturasBundle\Entity\date
     */
    public function getFechaIni()
    {
        return $this->fechaIni;
    }

    /**
     * @param boolean $mes
     */
    public function setMes($mes)
    {
        $this->mes = $mes;
    }

    /**
     * @return boolean
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * @param \FusionClone\FacturasBundle\Entity\date $proxFecha
     */
    public function setProxFecha($proxFecha)
    {
        $this->proxFecha = $proxFecha;
    }

    /**
     * @return \FusionClone\FacturasBundle\Entity\date
     */
    public function getProxFecha()
    {
        return $this->proxFecha;
    }

    /**
     * @param boolean $semana
     */
    public function setSemana($semana)
    {
        $this->semana = $semana;
    }

    /**
     * @return boolean
     */
    public function getSemana()
    {
        return $this->semana;
    }

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

} 