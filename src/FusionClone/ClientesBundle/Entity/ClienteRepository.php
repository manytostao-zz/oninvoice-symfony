<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\ClientesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class ClienteRepository extends EntityRepository
{
    public function findByName($nombre)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM ClientesBundle:Cliente ne WHERE ne.nombre LIKE :nombre'
        );

        $consulta->setParameter('nombre', '%' . $nombre . '%', 'string');

        return $consulta->getResult();

    }

    public function findByNameAndStatus($nombre, $status)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM ClientesBundle:Cliente ne WHERE ne.nombre LIKE :nombre AND ne.activo = :status'
        );
        $consulta->setParameter('nombre', '%' . $nombre . '%', 'string');
        $consulta->setParameter('status', $status, 'string');

        return $consulta->getResult();

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
        $alias = 'r';
        /* DB table to use */
        $tableObjectName = 'ClientesBundle:Cliente';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->distinct(true);

        if (isset($get['start']) && $get['length'] != '-1') {
            $cb->setFirstResult((int)$get['start'])
                ->setMaxResults((int)$get['length']);
        }
        /*
        * Ordering
        */
        /*Para cuando tengo un solo buscador*/
        if (isset($get['search']) && $get['search']['value'] != '') {
            $aLike = array();
            for ($i = 0; $i < count($get['columns']); $i++) {
                $colName = $get['columns'][$i];
                switch ($colName) {
                    default:
                        if ($colName != 'id' and $colName != 'saldo') {
                            $aLike[] = $cb->expr()->like('r.' . $colName, '\'%' . $get['search']['value'] . '%\'');
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

        if (isset($get['order'])) {
            for ($i = 0; $i < intval($get['order']); $i++) {
                $dir = $get['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                    $cb->orderBy($alias . '.' . $get['columns'][intval($get['order'][$i]['column'])], $dir);

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
                                $aLike[] = $cb->expr()->eq('r.' . $clave, $valor);
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
            ->getRepository('ClientesBundle:Cliente')
            ->createQueryBuilder('c')
            ->distinct(true);

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}