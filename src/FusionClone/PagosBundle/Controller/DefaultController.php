<?php

namespace FusionClone\PagosBundle\Controller;

use FusionClone\PagosBundle\Form\PagoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FusionClone\PagosBundle\Entity\Pago;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package FusionClone\PagosBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();
        $session->remove('filters');

        return $this->render('PagosBundle:Default:index.html.twig');
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $pago = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')->find($id);

        $pagoType = new PagoType();
        $pagoType->setFactId($pago->getFactura()->getId());
        $pagoType->setAction($this->generateUrl('pagos_save'));
        $pagoType->setTipoForm('edit');
        $metodos = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->findAll();
        $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
        $pagoForm = $this->createForm($pagoType, $pago);

        return $this->render('PagosBundle:Default:edit.html.twig', array('formPago' => $pagoForm->createView()));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveAction()
    {
        $form = $this->getRequest()->get('formPago');
        $oldAmmount = 0;
        if ($form['tipoForm'] == 'add') {
            $pago = new Pago();
        } elseif ($form['tipoForm'] == 'edit') {
            $pago = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')->find($form['id']);
            $oldAmmount = $pago->getImporte();
        }
        $pagoType = new PagoType();
        $metodos = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->findAll();
        $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
        $pagoForm = $this->createForm($pagoType, $pago);

        $pagoForm->handleRequest($this->getRequest());

        if ($pagoForm->isValid()) {
            $factura = $this->getDoctrine()->getManager()->getRepository('FacturasBundle:Factura')->find(
                $form['factId']
            );
            $pago->setFactura($factura);
            if ($form['tipoForm'] == 'add') {
                if (($factura->getSaldo() - $pago->getImporte()) <= 0) {
                    $pago->setImporte($factura->getSaldo());
                    $factura->setSaldo(0);
                    $factura->setEstado(
                        $this->getDoctrine()->getManager()->getRepository(
                            'NomencladoresBundle:NomEsta'
                        )->findByTdocAndStatus('FAC', 'Pagado')
                    );
                } else {
                    $factura->setSaldo($factura->getSaldo() - $pago->getImporte());
                }
            } elseif ($form['tipoForm'] == 'edit') {
                if (($factura->getSaldo() + $oldAmmount - $pago->getImporte()) <= 0) {
                    $pago->setImporte($factura->getSaldo());
                    $factura->setSaldo(0);
                    $factura->setEstado(
                        $this->getDoctrine()->getManager()->getRepository(
                            'NomencladoresBundle:NomEsta'
                        )->findByTdocAndStatus('FAC', 'Pagado')
                    );
                } else {
                    $factura->setSaldo($factura->getSaldo() + $oldAmmount - $pago->getImporte());
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($pago);
            $em->persist($factura);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_add',
                'Pago agregado exitosamente'
            );

            return $this->redirect($this->generateUrl('facturas_detail', array('id' => $pago->getFactura()->getId())));
        }

        $this->get('session')->getFlashBag()->add(
            'info_error',
            'Hay errores con los datos del pago. No agregado.'
        );
        if ($form['tipoForm'] == 'add') {
            return $this->redirect($this->generateUrl('facturas_homepage'));
        } elseif ($form['tipoForm'] == 'edit') {
            return $this->render('PagosBundle:Default:edit.html.twig', array('formPago' => $pagoForm->createView()));
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public
    function deleteAction(
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $pago = $em->find('PagosBundle:Pago', $id);
        $factura = $em->find('FacturasBundle:Factura', $pago->getFactura()->getId());
        $factura->setSaldo($pago->getImporte() + $factura->getSaldo());

        $em->persist($factura);
        $em->remove($pago);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Pago eliminado exitosamente'
        );

        return $this->redirect($this->generateUrl('pagos_homepage'));
    }

    /*AJAXSOURCE*/
    /**
     * @param Request $request
     * @return Response
     */
    public function listAjaxAction(Request $request)
    {
        $get = $request->request->all();
        $session = $request->getSession();
        $filters = $session->get('filters') != null ? $filters = $session->get('filters') : array();

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('id', 'fechaPago', 'fechaFactura', 'factura', 'cliente', 'importe', 'metodoPago', 'nota');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $monedaBase = $em->getRepository('OtrosBundle:AppParam')->findBy(array('nombre' => 'MONEDA_BASE'));
        $monedaBase = $em->getRepository('NomencladoresBundle:NomMone')->findBy(
            array('codigo' => $monedaBase[0]->getValor())
        );
        $monedaBase = $monedaBase[0];
        $cantidadDecimales = $em->getRepository('OtrosBundle:AppParam')->findBy(
            array('nombre' => 'CANTIDAD_DECIMALES')
        );
        $cantidadDecimales = $cantidadDecimales[0];
        $rResult = $em->getRepository('PagosBundle:Pago')->ajaxTable(
            $get,
            $filters,
            true,
            $this->get('security.context')->getToken()->getUser()
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('PagosBundle:Pago')->getCount());
        $query = $em->getRepository('PagosBundle:Pago')->ajaxTable(
            $get,
            $filters,
            true,
            $this->get('security.context')->getToken()->getUser()
        );
        $query->setFirstResult(null)->setMaxResults(null);
        $iFilteredTotal = count($query->getArrayResult());

        /*
        * Output
        */
        $output = array(
            "draw" => intval($request->get('draw')),
            "recordsTotal" => intval($iRecordsTotal),
            "recordsFiltered" => intval($iFilteredTotal),
            "data" => array()
        );
        foreach ($rResult as $aRow) {
            $row = array();
            for ($i = 0; $i < count($columns); $i++) {
                switch ($columns[$i]) {
                    case "fechaPago":
                        if (is_null($aRow['fecha'])) {
                            $row[] = '';
                        } else {
                            $date = getdate($aRow['fecha']->getTimestamp());
                            if ($date['mday'] < 10) {
                                if ($date['mon'] < 10) {
                                    $row[] = '0' . $date['mon'] . '/' . '0' . $date['mday'] . '/' . $date['year'];
                                } else {
                                    $row[] = '0' . $date['mon'] . '/' . $date['mday'] . '/' . $date['year'];
                                }
                            } else {
                                if ($date['mon'] < 10) {
                                    $row[] = $date['mon'] . '/' . '0' . $date['mday'] . '/' . $date['year'];
                                } else {
                                    $row[] = $date['mon'] . '/' . $date['mday'] . '/' . $date['year'];
                                }
                            }
                        }
                        break;
                    case 'fechaFactura':
                        if (is_null($aRow['factura']['fecha'])) {
                            $row[] = '';
                        } else {
                            $date = getdate($aRow['factura']['fecha']->getTimestamp());
                            if ($date['mday'] < 10) {
                                if ($date['mon'] < 10) {
                                    $row[] = '0' . $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                } else {
                                    $row[] = '0' . $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                }
                            } else {
                                if ($date['mon'] < 10) {
                                    $row[] = $date['mday'] . '/' . '0' . $date['mon'] . '/' . $date['year'];
                                } else {
                                    $row[] = $date['mday'] . '/' . $date['mon'] . '/' . $date['year'];
                                }
                            }
                        }
                        break;
                    case 'factura':
                        $row[] = '<a href="' . $this->generateUrl(
                                'facturas_detail',
                                array('id' => $aRow['factura']['id'])
                            ) . '">' . $aRow['factura']['codigo'] . '</a>';
                        break;
                    case 'cliente':
                        $row[] = '<a href="' . $this->generateUrl(
                                'clientes_details',
                                array('id' => $aRow['factura']['cliente']['id'])
                            ) . '">' . $aRow['factura']['cliente']['nombre'] . '</a>';
                        break;
                    case 'metodoPago':
                        $row[] = $aRow['metodo']['nombre'];
                        break;
                    case 'importe':
                        if ($monedaBase->getUbicaSimbol()) {
                            $row[] = $monedaBase->getSimbolo() . number_format(
                                    $aRow[$columns[$i]],
                                    $cantidadDecimales->getValor(),
                                    $monedaBase->getSignDecimal(),
                                    $monedaBase->getSignMillares()
                                );
                        } else {
                            $row[] = number_format(
                                    $aRow[$columns[$i]],
                                    $cantidadDecimales->getValor(),
                                    $monedaBase->getSignDecimal(),
                                    $monedaBase->getSignMillares()
                                ) . $monedaBase->getSimbolo();
                        }
                        break;
                    default:
                        if ($columns[$i] != ' ') {
                            /* General output */
                            $row[] = $aRow[$columns[$i]];
                        }
                        break;
                }
            }
            $output['data'][] = $row;
        }
        unset($rResult);

        return new Response(
            json_encode($output)
        );
    }
}
