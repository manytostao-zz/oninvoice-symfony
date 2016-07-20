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
 * @ORM\Entity(repositoryClass="FusionClone\NomencladoresBundle\Entity\NomEstaRepository")
 * @ORM\Table(name="nomesta")
 */
class NomEsta {
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $tdoc
     */
    public function setTdoc($tdoc)
    {
        $this->tdoc = $tdoc;
    }

    /**
     * @return mixed
     */
    public function getTdoc()
    {
        return $this->tdoc;
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

    public function __toString(){
        return $this->getDescripcion();
    }
} 