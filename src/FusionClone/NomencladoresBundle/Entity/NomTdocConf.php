<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 19/09/14
 * Time: 19:00
 */

namespace FusionClone\NomencladoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FusionClone\NomencladoresBundle\Entity\NomTdocConfRepository")
 * @ORM\Table(name="nomtdocconf")
 */

class NomTdocConf
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
     * @ORM\ManyToOne(targetEntity="FusionClone\NomencladoresBundle\Entity\NomTdoc")
     */
    protected $tdoc;

    /** @ORM\Column(type="string", length=100) */
    protected $descripcion;

    /** @ORM\Column(type="integer")*/
    protected $consecutivo;

    /** @ORM\Column(type="string", length=100) */
    protected $prefijo;

    /** @ORM\Column(type="integer") */
    protected $cantDigCons;

    /** @ORM\Column(name="anno", type="boolean") */
    protected $anno;

    /** @ORM\Column(name="mes", type="boolean") */
    protected $mes;




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
    public function setPrefijo($codigo)
    {
        $this->prefijo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getPrefijo()
    {
        return $this->prefijo;
    }

    /**
     * @param mixed $consecutivo
     */
    public function setConsecutivo($consecutivo)
    {
        $this->consecutivo = $consecutivo;
    }

    /**
     * @return mixed
     */
    public function getConsecutivo()
    {
        return $this->consecutivo;
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
     * @param int $tdoc
     */
    public function setTdoc($tdoc)
    {
        $this->tdoc = $tdoc;
    }

    /**
     * @return int
     */
    public function getTdoc()
    {
        return $this->tdoc;
    }

    /**
     * @param mixed $anno
     */
    public function setAnno($anno)
    {
        $this->anno = $anno;
    }

    /**
     * @return mixed
     */
    public function getAnno()
    {
        return $this->anno;
    }

    /**
     * @param mixed $cantDigCons
     */
    public function setCantDigCons($cantDigCons)
    {
        $this->cantDigCons = $cantDigCons;
    }

    /**
     * @return mixed
     */
    public function getCantDigCons()
    {
        return $this->cantDigCons;
    }

    /**
     * @param mixed $mes
     */
    public function setMes($mes)
    {
        $this->mes = $mes;
    }

    /**
     * @return mixed
     */
    public function getMes()
    {
        return $this->mes;
    }



    public function __toString(){
        return $this->getDescripcion();
    }

} 