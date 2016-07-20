<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 10/07/14
 * Time: 9:43
 */

namespace FusionClone\PagosBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;


/**
 * Class PagoRepository
 * @package FusionClone\PagosBundle\Entity
 */
class PagoRepository extends EntityRepository
{
    /**
     * @param $cliente
     * @return array
     */
    public function findByCliente($cliente)
    {
        $em = $this->getEntityManager();
        $consulta = $em->createQuery(
            'SELECT ne FROM PagosBundle:Pago ne JOIN FacturasBundle:Factura nt WHERE ne.factura = nt.id AND nt.cliente = :cliente'
        );
        $consulta->setParameter('cliente', $cliente);

        return $consulta->getResult();

    }

    /**
     * @param array $get
     * @param array $filters
     * @param bool $flag
     * @return array|\Doctrine\ORM\Query
     */
    public function ajaxTable(array $get, array $filters, $flag = false, $user)
    {
        /* Indexed column (used for fast and accurate table cardinality) */
        $alias = 'p';
        /* DB table to use */
        $tableObjectName = 'PagosBundle:Pago';
        /**
         * Set to default
         */
        $cb = $this->getEntityManager()
            ->getRepository($tableObjectName)
            ->createQueryBuilder($alias)
            ->addSelect('f, m', 'c')
            ->leftJoin('p.factura', 'f')
            ->leftJoin('f.cliente', 'c')
            ->leftJoin('p.metodo', 'm');

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
                    case 'fechaPago':
                        $cb->orderBy('p.fecha', $dir);
                        break;
                    case 'fechaFactura':
                        $cb->orderBy('f.fecha', $dir);
                        break;
                    case 'factura':
                        $cb->orderBy('f.codigo', $dir);
                        break;
                    case 'cliente':
                        $cb->orderBy('c.nombre', $dir);
                        break;
                    case 'metodoPago':
                        $cb->orderBy('m.nombre', $dir);
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
                    case 'fechaPago':
                        $aLike[] = $cb->expr()->like('p.fecha', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'fechaFactura':
                        $aLike[] = $cb->expr()->like('f.fecha', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'factura':
                        $aLike[] = $cb->expr()->like('f.codigo', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'cliente':
                        $aLike[] = $cb->expr()->like('c.nombre', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    case 'metodoPago':
                        $aLike[] = $cb->expr()->like('m.nombre', '\'%' . $get['search']['value'] . '%\'');
                        break;
                    default:
                        if ($colName != 'id') {
                            $aLike[] = $cb->expr()->like('p.' . $colName, '\'%' . $get['search']['value'] . '%\'');
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
                                $aLike[] = $cb->expr()->eq('p.' . $clave, $valor);
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

        /*Si se ha logueado un cliente*/
        if (!is_null($user->getCliente()))
        {
            $cb->andWhere('c.id' == $user->getCliente()->getId());
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
            ->getRepository('PagosBundle:Pago')
            ->createQueryBuilder('p');

        $aResultTotal = count($query->getQuery()->getArrayResult());

        return $aResultTotal;
    }

}