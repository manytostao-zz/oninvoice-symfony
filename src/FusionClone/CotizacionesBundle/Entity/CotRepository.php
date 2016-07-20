<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\CotizacionesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


class CotRepository extends EntityRepository
{
    public function findByStatus($status)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne WHERE ne.estado = :status ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('status', $status, 'string');

        return $consulta->getResult();

    }

    public function findByCod($codigo)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne WHERE ne.codigo LIKE :codigo ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('codigo', '%' . $codigo . '%', 'string');

        return $consulta->getResult();

    }

    public function findByCodStat($codigo, $status)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne
            WHERE ne.codigo LIKE :codigo AND ne.estado = :status ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('status', $status, 'string');
        $consulta->setParameter('codigo', '%' . $codigo . '%', 'string');

        return $consulta->getResult();

    }

    public function findByCodClie($codigo, $cliente)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne
            WHERE ne.codigo LIKE :codigo AND ne.cliente = :cliente ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('cliente', $cliente, 'string');
        $consulta->setParameter('codigo', '%' . $codigo . '%', 'string');

        return $consulta->getResult();

    }

    public function findByCodAndClie($codigo, $cliente)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne WHERE ne.codigo LIKE :codigo AND ne.cliente = :cliente ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('status', '%' . $codigo . '%', 'string');
        $consulta->setParameter('status', $cliente->getId(), 'string');

        return $consulta->getResult();

    }

    public function findByCodClieStat($codigo, $cliente, $status)
    {

        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM CotizacionesBundle:Cotizacion ne
            WHERE ne.codigo LIKE :codigo AND ne.cliente = :cliente AND ne.estado = :status
            ORDER BY ne.fechaVenc'
        );

        $consulta->setParameter('codigo', '%' . $codigo . '%', 'string');
        $consulta->setParameter('status', $status, 'string');
        $consulta->setParameter('cliente', $cliente->getId(), 'integer');

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
        $tableObjectName = 'CotizacionesBundle:Cotizacion';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('u', 'e', 'c', 'm')
            ->distinct(true)
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.cliente', 'c')
            ->leftJoin('r.moneda', 'm')
            ->leftJoin('r.estado', 'e');

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
                    case 'estado':
                        $cb->orderBy('e.descripcion', $dir);
                        break;
                    case 'cliente':
                        $cb->orderBy('c.nombre', $dir);
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
                    case "estado":
                        $aLike[] = $cb->expr()->like('e.descripcion', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case "cliente":
                        $aLike[] = $cb->expr()->like('c.nombre', '\'%' . $get['search']['value'] . '%\'');
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
                        case 'estado':
                            $aLike[] = $cb->expr()->eq('e.descripcion', "'" . $valor . "'");
                            break;
                        case 'cliente':
                            $aLike[] = $cb->expr()->eq('c.id', $valor);
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
    public function getCount()
    {
        $query = $this->getEntityManager()
            ->getRepository('CotizacionesBundle:Cotizacion')
            ->createQueryBuilder('r')
            ->addSelect('u', 'e', 'c', 'm')
            ->distinct(true)
            ->leftJoin('r.usuario', 'u')
            ->leftJoin('r.cliente', 'c')
            ->leftJoin('r.moneda', 'm')
            ->leftJoin('r.estado', 'e');

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}