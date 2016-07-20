<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\FacturasBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class FacturaItemRepository extends EntityRepository
{
    /**
     * @param array $get
     * @param array $filters
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     */
    public function prodOfer(array $get, array $filters, $flag = false)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'fi';
        /* DB table to use */
        $tableObjectName = 'FacturasBundle:FacturaItem';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('f', 'c', 'p')
            ->distinct(true)
            ->leftJoin('fi.factura', 'f')
            ->leftJoin('f.cliente', 'c')
            ->leftJoin('fi.producto', 'p');

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
                    case 'producto':
                        $cb->orderBy('p.nombre', $dir);
                        break;
                    case 'cliente':
                        $cb->orderBy('c.nombre', $dir);
                        break;
                    case 'factura':
                        $cb->orderBy('f.codigo', $dir);
                        break;
                        break;
                    case 'fecha':
                        $cb->orderBy('f.fecha', $dir);
                        break;
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
                    case "producto":
                        $aLike[] = $cb->expr()->like('p.nombre', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case "cliente":
                        $aLike[] = $cb->expr()->like('c.nombre', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case "factura":
                        $aLike[] = $cb->expr()->like('f.codigo', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case "fecha":
                        $aLike[] = $cb->expr()->like('f.fecha', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    default:
                        if ($colName != 'id') {
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
        /*Resto de filtros*/
        if (!is_null($filters)) {
            $aLike = array();
            $i = 0;
            foreach ($filters as $clave => $valor) {
                if (!is_null($valor)) {
                    switch ($clave) {
                        case "fechaDesde":
                            if (!is_null($valor) and $valor != '') {
                                /*$fechaDesde = new \DateTime('today', new \DateTimeZone('America/Lima'));*/
                                $aLike[] = $cb->expr()->gte('f.fecha', ':fechaDesde');
                                $cb->setParameter('fechaDesde', $valor, 'date');
                            }
                            break;
                        case "fechaHasta":
                            if (!is_null($valor) and $valor != '') {
                                /*$fechaHasta = new \DateTime('today', new \DateTimeZone('America/Lima'));*/
                                $aLike[] = $cb->expr()->lte('f.fecha', ':fechaHasta');
                                $cb->setParameter('fechaHasta', $valor, 'date');
                            }
                            break;
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
    public function prodOferCount()
    {
        $query = $this->getEntityManager()
            ->getRepository('FacturasBundle:FacturaItem')
            ->createQueryBuilder('fi')
            ->addSelect('f', 'c', 'p')
            ->distinct(true)
            ->leftJoin('fi.factura', 'f')
            ->leftJoin('f.cliente', 'c')
            ->leftJoin('fi.producto', 'p');

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}