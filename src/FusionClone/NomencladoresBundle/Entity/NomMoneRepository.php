<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\NomencladoresBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class NomMoneRepository extends EntityRepository
{
    public function findByCodOrName($codigo, $nombre)
    {
        if (!is_null($codigo) || !is_null($nombre)){
            $em = $this->getEntityManager();
            $consulta = $em->createQuery(
                'SELECT ne FROM NomencladoresBundle:NomMone ne
                    WHERE ne.codigo LIKE :codigo OR ne.descripcion LIKE :nombre'
            );


            $consulta->setParameter('codigo', '%'.$codigo.'%', 'string');
            $consulta->setParameter('nombre', '%'.$nombre.'%', 'string');

            return $consulta->getResult();
        }

        return null;
    }

    /**
     * @param array $get
     * @param array $filters
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     */
    public function ajaxTable(array $get, array $filters, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'm';
        /* DB table to use */
        $tableObjectName = 'NomencladoresBundle:NomMone';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias);

        if (isset($get['start']) && $get['length'] != '-1') {
            $cb->setFirstResult((int)$get['start'])
                ->setMaxResults((int)$get['length']);
        }
        /*
        * Ordering
        */
        if (isset($get['order'])) {
            for ($i = 0; $i < intval($get['order']); $i++) {
                $dir = $get['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                switch ($get['columns'][intval($get['order'][$i]['column'])]) {
                    default:
                        $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);
                        break;
                }

            }
        }
        /*Para cuando tengo un solo buscador*/
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    default:
                        if ($colName != 'id') {
                            $aLike[] = $cb->expr()->like('m.' . $colName, '\'%' . $get['search']['value'] . '%\'');
                        }
                        break;
                }
            }
            if (count($aLike) > 0) {
                $cb->andWhere(new Orx($aLike));
            } else {
                unset($aLike);
            }
        }
        /*Resto de filtros*/
        if (!is_null($filters)) {
            $aLike = array();
            $i = 0;
            foreach ($filters as $clave => $valor) {
                if (!is_null($valor)) {
                    switch ($clave) {
                        default:
                            if (!is_null($valor)) {
                                $aLike[] = $cb->expr()->eq('m.' . $clave, $valor);
                            }
                            break;
                    }

                }
                $i = $i + 1;
            }
            if (count($aLike) > 0) {
                $cb->andWhere(new Andx($aLike));
            } else {
                unset($aLike);
            }
        }

        /*
        * SQL queries
        * Get data to display
        */
        $query = $cb->getQuery();
        if ($flag) {
            return $query;
        } else {
            $results = $query->getResult();

            return $results;
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $query = $this->getEntityManager()
            ->getRepository('NomencladoresBundle:NomMone')
            ->createQueryBuilder('m');

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }


}