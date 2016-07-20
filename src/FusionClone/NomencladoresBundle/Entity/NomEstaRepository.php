<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\NomencladoresBundle\Entity;

use Doctrine\ORM\EntityRepository;


class NomEstaRepository extends EntityRepository
{
    public function findByTdocAndStatus($tdoc, $status)
    {
        if (!is_null($tdoc) && !is_null($status)){
            $em = $this->getEntityManager();
            $consulta = $em->createQuery(
                'SELECT ne FROM NomencladoresBundle:NomEsta ne JOIN NomencladoresBundle:NomTdoc nt
                    WHERE ne.tdoc = nt.id AND nt.codigo = :cod AND ne.descripcion =:esta'
            );


            $consulta->setParameter('cod', $tdoc, 'string');
            $consulta->setParameter('esta', $status, 'string');

            return $consulta->getSingleResult();
        }

        return null;
    }

}