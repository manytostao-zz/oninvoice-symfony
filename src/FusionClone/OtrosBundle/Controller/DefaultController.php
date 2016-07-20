<?php

namespace FusionClone\OtrosBundle\Controller;

use Assetic\Factory\Worker\EnsureFilterWorker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use FusionClone\OtrosBundle\OverridenClasses\ChoiceListStringValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $sesion = $this->getRequest()->getSession();
        $parametrosGenerales = $this->getDoctrine()->getManager()->getRepository('OtrosBundle:AppParam')->findByNombre(
            'MONEDA_BASE'
        );
        $monedaBase = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomMone')->findByCodigo(
            $parametrosGenerales[0]->getValor()
        );
        $sesion->set('MONEDA_BASE', $monedaBase);
        $this->getRequest()->setSession($sesion);
        $classActive = array('sup' => 'Inicio', 'sub' => 'Ver');
        $usuario = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        /*FACTURAS*/
        $tdoc = $em->getRepository('NomencladoresBundle:NomTdoc')->findByCodigo('FAC');
        $estados = $em->getRepository('NomencladoresBundle:NomEsta')->findByTdoc($tdoc);
        foreach ($estados as $estado) {
            switch ($estado->getDescripcion()) {
                case 'Borrador':
                    if (!is_null($usuario->getCliente())) {
                        $facturasBorrador = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $facturasBorrador = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalFactBorrador = 0.000;
                    foreach ($facturasBorrador as $factura) {
                        $totalFactBorrador = $totalFactBorrador + $factura->getImporte();
                    }
                    break;

                case 'Enviado':
                    if (!is_null($usuario->getCliente())) {
                        $facturasEnviadas = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $facturasEnviadas = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalFactEnviadas = 0.000;
                    foreach ($facturasEnviadas as $factura) {
                        $totalFactEnviadas = $totalFactEnviadas + $factura->getImporte();
                    }
                    break;

                case 'Vencido':
                    if (!is_null($usuario->getCliente())) {
                        $facturasVencidas = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $facturasVencidas = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:Factura'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalFactVencidas = 0.000;
                    foreach ($facturasVencidas as $factura) {
                        $totalFactVencidas = $totalFactVencidas + $factura->getImporte();
                    }
                    break;
            }
        }

        /*PAGOS*/
        if (!is_null($usuario->getCliente())) {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT pa FROM PagosBundle:Pago pa JOIN pa.factura fa WHERE fa.cliente = :cliente'
            );

            $consulta->setParameter('cliente', $usuario->getCliente());
            $pagos = $consulta->getResult();
        } else {
            $pagos = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')->findAll();
        }
        $totalPagos = 0.000;
        foreach ($pagos as $pago) {
            $totalPagos = $totalPagos + $pago->getImporte();
        }

        /*COTIZACIONES*/
        $tdoc = $em->getRepository('NomencladoresBundle:NomTdoc')->findByCodigo('COT');
        $estados = $em->getRepository('NomencladoresBundle:NomEsta')->findByTdoc($tdoc);
        foreach ($estados as $estado) {
            switch ($estado->getDescripcion()) {
                case 'Borrador':
                    if (!is_null($usuario->getCliente())) {
                        $cotizacionesBorrador = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $cotizacionesBorrador = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalCotBorrador = 0.000;
                    foreach ($cotizacionesBorrador as $cotizacion) {
                        $totalCotBorrador = $totalCotBorrador + $cotizacion->getImporte();
                    }
                    break;

                case 'Enviado':
                    if (!is_null($usuario->getCliente())) {
                        $cotizacionesEnviadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $cotizacionesEnviadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalCotEnviadas = 0.000;
                    foreach ($cotizacionesEnviadas as $cotizacion) {
                        $totalCotEnviadas = $totalCotEnviadas + $cotizacion->getImporte();
                    }
                    break;

                case 'Rechazado':
                    if (!is_null($usuario->getCliente())) {
                        $cotizacionesRechazadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $cotizacionesRechazadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalCotRechazadas = 0.000;
                    foreach ($cotizacionesRechazadas as $cotizacion) {
                        $totalCotRechazadas = $totalCotRechazadas + $cotizacion->getImporte();
                    }
                    break;

                case 'Aprobado':
                    if (!is_null($usuario->getCliente())) {
                        $cotizacionesAprobadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado, 'cliente' => $usuario->getCliente())
                        );
                    } else {

                        $cotizacionesAprobadas = $this->getDoctrine()->getManager()->getRepository(
                            'CotizacionesBundle:Cotizacion'
                        )->findBy(
                            array('estado' => $estado)
                        );
                    }
                    $totalCotAprobadas = 0.000;
                    foreach ($cotizacionesAprobadas as $cotizacion) {
                        $totalCotAprobadas = $totalCotAprobadas + $cotizacion->getImporte();
                    }
                    break;
            }
        }

        if (!is_null($usuario->getCliente())) {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT fac FROM FacturasBundle:Factura fac WHERE fac.cliente = :cliente ORDER BY fac.fecha DESC'
            );
            $consulta->setParameter('cliente', $usuario->getCliente());
        } else {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT fac FROM FacturasBundle:Factura fac ORDER BY fac.fecha DESC'
            );
        }
        $consulta->setMaxResults(1);
        $lastFactura = $consulta->getResult();

        if (!is_null($usuario->getCliente())) {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT cot FROM CotizacionesBundle:Cotizacion cot WHERE cot.cliente = :cliente ORDER BY cot.fecha DESC'
            );
            $consulta->setParameter('cliente', $usuario->getCliente());
        } else {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT cot FROM CotizacionesBundle:Cotizacion cot ORDER BY cot.fecha DESC'
            );
        }
        $consulta->setMaxResults(1);
        $lastCotizacion = $consulta->getResult();

        if (!is_null($usuario->getCliente())) {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT pago FROM PagosBundle:Pago pago JOIN pago.factura fac WHERE fac.cliente = :cliente ORDER BY pago.fecha DESC'
            );
            $consulta->setParameter('cliente', $usuario->getCliente());
        } else {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT pago FROM PagosBundle:Pago pago ORDER BY pago.fecha DESC'
            );
        }
        $consulta->setMaxResults(1);
        $lastPago = $consulta->getResult();


        return $this->render(
            'OtrosBundle:Default:index.html.twig',
            array(
                'totalFactBorrador' => $totalFactBorrador,
                'totalFactEnviadas' => $totalFactEnviadas,
                'totalFactVencidas' => $totalFactVencidas,
                'totalCotBorrador' => $totalCotBorrador,
                'totalCotEnviadas' => $totalCotEnviadas,
                'totalCotRechazadas' => $totalCotRechazadas,
                'totalCotAprobadas' => $totalCotAprobadas,
                'totalPagos' => $totalPagos,
                'active' => $classActive,
                'lastFactura' => $lastFactura,
                'lastCotizacion' => $lastCotizacion,
                'lastPago' => $lastPago
            )
        );
    }

    public function prodOferAction()
    {
        $classActive = array('sup' => 'Informes', 'sub' => 'prodOfer');
        $peticion = $this->getRequest();
        $session = $peticion->getSession();
        $filters = $session->get('filters');
        $form = $peticion->get('form');
        $productos = null;
        $prodOfers = null;

        $dateForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('prodOfer'))
            ->add(
                'desde',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/y',
                    'data' => !is_null($filters) ? array_key_exists(
                        'fechaDesde',
                        $filters
                    ) ? $filters['fechaDesde'] : new \DateTime('today') : new \DateTime('today'),
                    'invalid_message' => 'El campo "Desde" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'hasta',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/y',
                    'data' => !is_null($filters) ? array_key_exists(
                        'fechaHasta',
                        $filters
                    ) ? $filters['fechaHasta'] : new \DateTime('today') : new \DateTime('today'),
                    'invalid_message' => 'El campo "Hasta" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('Ejecutar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        if (is_null($filters) or !array_key_exists('fechaDesde',$filters)) {
           $filters['fechaDesde'] = new \DateTime('today');
        }
        if (is_null($filters) or !array_key_exists('fechaHasta',$filters)) {
            $filters['fechaHasta'] = new \DateTime('today');
        }
        $session->set('filters', $filters);
        $dateForm->handleRequest($peticion);
        if ($dateForm->isValid()) {
            if ($peticion->getMethod() == 'POST') {
                $fechaDesde = new \DateTime();
                $fechaDesde->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsDesde = explode('/', $form['desde']);
                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[0], $fechaInsDesde[1]);

                $fechaHasta = new \DateTime();
                $fechaHasta->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsHasta = explode('/', $form['hasta']);
                $fechaHasta->setDate($fechaInsHasta[2], $fechaInsHasta[0], $fechaInsHasta[1]);

                $session->set('filters', array('fechaDesde' => $fechaDesde, 'fechaHasta' => $fechaHasta));
                $peticion->setSession($session);
            }
        }

        return $this->render(
            'OtrosBundle:Reportes:prodOfer.html.twig',
            array(
                'dateForm' => $dateForm->createView(),
                'active' => $classActive
            )
        );

    }

    public function prodOferAjaxAction(Request $request)
    {
        $get = $request->request->all();
        $session = $request->getSession();
        $filters = $session->get('filters') != null ? $filters = $session->get('filters') : array();

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('producto', 'cliente', 'factura', 'fecha', 'precio', 'cantidad', 'total');
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
        $rResult = $em->getRepository('FacturasBundle:FacturaItem')->prodOfer($get, $filters, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('FacturasBundle:FacturaItem')->prodOferCount());
        $query = $em->getRepository('FacturasBundle:FacturaItem')->prodOfer($get, $filters, true);
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
                    case "version":/* Special output formatting for 'version' column */
                        $row[] = ($aRow[$columns[$i]] == "0") ? '-' : $aRow[$columns[$i]];
                        break;
                    case 'producto':
                        $row[] = $aRow['producto']['nombre'];
                        break;
                    case "cliente":
                        $row[] = '<a href="' . $this->generateUrl(
                                'clientes_details',
                                array('id' => $aRow['factura'][$columns[$i]]['id'])
                            )
                            . '">' . $aRow['factura'][$columns[$i]]['nombre'] . '</a>';
                        break;
                    case "factura":
                        $row[] = '<a href="' . $this->generateUrl(
                                'clientes_details',
                                array('id' => $aRow[$columns[$i]]['id'])
                            )
                            . '">' . $aRow[$columns[$i]]['codigo'] . '</a>';
                        break;
                    case "fecha":
                        if (is_null($aRow['factura']['fecha'])) {
                            $row[] = '';
                        } else {
                            $date = getdate($aRow['factura'][$columns[$i]]->getTimestamp());
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
                    case "precio":
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
                    case "total":
                        if ($monedaBase->getUbicaSimbol()) {
                            $row[] = $monedaBase->getSimbolo() . number_format(
                                    $aRow['precio'] * $aRow['cantidad'],
                                    $cantidadDecimales->getValor(),
                                    $monedaBase->getSignDecimal(),
                                    $monedaBase->getSignMillares()
                                );
                        } else {
                            $row[] = number_format(
                                    $aRow['precio'] * $aRow['cantidad'],
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

    public function pagosRecoAction()
    {
        $classActive = array('sup' => 'Informes', 'sub' => 'pagosReco');
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $form = $peticion->get('form');
        $pagosReco = null;

        $dateForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('pagosReco'))
            ->add(
                'desde',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'invalid_message' => 'El campo "Desde" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'hasta',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'invalid_message' => 'El campo "Hasta" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('Ejecutar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        $dateForm->handleRequest($peticion);
        if ($dateForm->isValid()) {
            if ($peticion->getMethod() == 'POST') {

                $peticion = $this->getRequest();
                $sesion = $peticion->getSession();

                //Creando el paginador
                $itemsPerPage = $peticion->get('itemsAmmount');
                if (!$itemsPerPage) {
                    if (!$sesion->has('itemsPerPage') || (!$sesion->get('itemsPerPage'))) {
                        $sesion->set('itemsPerPage', $this->container->getParameter('paginator.items_per_page'));
                    }
                } else {
                    if ($itemsPerPage > 0) {
                        $sesion->set('itemsPerPage', $itemsPerPage);
                    }
                }
                $paginador = $this->get('ideup.simple_paginator');

                if ($itemsPerPage) {

                    $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
                } else {
                    $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
                }
                $paginador->setMaxPagerItems(6);

                $peticion->setSession($sesion);
                $consulta = $em->createQuery(
                    'SELECT pago
                    FROM PagosBundle:Pago pago
                        JOIN pago.factura fa
                    WHERE pago.fecha BETWEEN :desde AND :hasta'
                );
                $fechaDesde = new \DateTime();
                $fechaDesde->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsDesde = explode('/', $form['desde']);
                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                $consulta->setParameter('desde', $fechaDesde, 'date');
                $fechaHasta = new \DateTime();
                $fechaHasta->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsHasta = explode('/', $form['hasta']);
                $fechaHasta->setDate($fechaInsDesde[2], $fechaInsHasta[1], $fechaInsHasta[0]);
                $consulta->setParameter('hasta', $fechaHasta, 'date');
                $pagosReco = $paginador->paginate($consulta->getResult())->getResult();

                return $this->render(
                    'OtrosBundle:Reportes:pagosReco.html.twig',
                    array(
                        'dateForm' => $dateForm->createView(),
                        'pagosReco' => $pagosReco,
                        'paginador' => $paginador,
                        'itemsPerPage' => $sesion->get('itemsPerPage'),
                        'active' => $classActive
                    )
                );
            }
        }

        return $this->render(
            'OtrosBundle:Reportes:pagosReco.html.twig',
            array(
                'dateForm' => $dateForm->createView(),
                'active' => $classActive
            )
        );

    }

    public function ingClieAction()
    {
        $classActive = array('sup' => 'Informes', 'sub' => 'ingClie');
        $peticion = $this->getRequest();
        $form = $peticion->get('form');
        $em = $this->getDoctrine()->getManager();
        $fechas = $em->createQuery(
            '
                    SELECT DISTINCT fa.fecha FROM FacturasBundle:Factura fa'
        )->getResult();
        $annos = array();
        foreach ($fechas as $fecha) {
            $fullFecha = getdate($fecha['fecha']->getTimeStamp());
            if (!in_array($fullFecha['year'], $annos)) {
                //array_push($annos, $fullFecha['year']);
                $annos[$fullFecha['year']] = $fullFecha['year'];
            }
        }
        $choice_list = new ChoiceListStringValue($annos, $annos);
        $dateForm = $this->createFormBuilder(array('anno' => $form['anno']))
            ->setAction($this->generateUrl('ingClie'))
            ->add(
                'anno',
                'choice',
                array(
                    'choice_list' => $choice_list,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('Ejecutar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        if ($peticion->getMethod() == 'POST') {

            $peticion = $this->getRequest();
            $sesion = $peticion->getSession();

            //Creando el paginador
            $itemsPerPage = $peticion->get('itemsAmmount');
            if (!$itemsPerPage) {
                if (!$sesion->has('itemsPerPage') || (!$sesion->get('itemsPerPage'))) {
                    $sesion->set('itemsPerPage', $this->container->getParameter('paginator.items_per_page'));
                }
            } else {
                if ($itemsPerPage > 0) {
                    $sesion->set('itemsPerPage', $itemsPerPage);
                }
            }
            $paginador = $this->get('ideup.simple_paginator');

            if ($itemsPerPage) {

                $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
            } else {
                $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
            }
            $paginador->setMaxPagerItems(6);

            $peticion->setSession($sesion);
            $consulta = $em->createQuery(
                'SELECT pago
                FROM PagosBundle:Pago pago
                    JOIN pago.factura fa
                  WHERE pago.fecha BETWEEN :desde AND :hasta
                GROUP BY pago, fa.cliente'
            );
            $consulta->setParameter('desde', new \DateTime('01/01/' . $form['anno']));
            $consulta->setParameter('hasta', new \DateTime('12/31/' . $form['anno']));
            $ingsClie = $paginador->paginate($consulta->getResult())->getResult();

            $id = 0;
            $desglose = array();
            foreach ($ingsClie as $ingClie) {
                if ($ingClie->getFactura()->getCliente()->getId() != $id) {
                    $id = $ingClie->getFactura()->getCliente()->getId();
                    $desglose[$ingClie->getFactura()->getCliente()->getNombre()] = array();
                }
                $fecha = getDate($ingClie->getFecha()->getTimeStamp());
                $desglose[$ingClie->getFactura()->getCliente()->getNombre()][$fecha['mon']] =
                    isset($desglose[$ingClie->getFactura()->getCliente()->getNombre()][$fecha['month']]) ?
                        $desglose[$ingClie->getFactura()->getCliente()->getNombre(
                        )][$fecha['month']] + $ingClie->getImporte() :
                        $ingClie->getImporte();
                $desglose[$ingClie->getFactura()->getCliente()->getNombre()]['total'] =
                    isset($desglose[$ingClie->getFactura()->getCliente()->getNombre()]['total']) ?
                        $desglose[$ingClie->getFactura()->getCliente()->getNombre()]['total'] + $ingClie->getImporte() :
                        $ingClie->getImporte();
            }

            $consulta = $em->createQuery(
                'SELECT pago
                FROM PagosBundle:Pago pago
                    JOIN pago.factura fa
                  WHERE pago.fecha BETWEEN :desde AND :hasta
                GROUP BY fa.cliente'
            );
            $consulta->setParameter('desde', new \DateTime('01/01/' . $form['anno']));
            $consulta->setParameter('hasta', new \DateTime('12/31/' . $form['anno']));
            $ingsClie = $paginador->paginate($consulta->getResult())->getResult();

            return $this->render(
                'OtrosBundle:Reportes:ingClie.html.twig',
                array(
                    'dateForm' => $dateForm->createView(),
                    'ingsClie' => $ingsClie,
                    'desglose' => $desglose,
                    'paginador' => $paginador,
                    'itemsPerPage' => $sesion->get('itemsPerPage'),
                    'active' => $classActive
                )
            );
        }

        return $this->render(
            'OtrosBundle:Reportes:ingClie.html.twig',
            array(
                'dateForm' => $dateForm->createView(),
                'active' => $classActive
            )
        );

    }

    public function resImpsAction()
    {
        $classActive = array('sup' => 'Informes', 'sub' => 'resImps');
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $form = $peticion->get('form');
        $resImps = null;

        $dateForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('resImps'))
            ->add(
                'desde',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'invalid_message' => 'El campo "Desde" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'hasta',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'invalid_message' => 'El campo "Hasta" posee un valor inválido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('Ejecutar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        $dateForm->handleRequest($peticion);
        if ($dateForm->isValid()) {
            if ($peticion->getMethod() == 'POST') {

                $peticion = $this->getRequest();
                $sesion = $peticion->getSession();

                //Creando el paginador
                $itemsPerPage = $peticion->get('itemsAmmount');
                if (!$itemsPerPage) {
                    if (!$sesion->has('itemsPerPage') || (!$sesion->get('itemsPerPage'))) {
                        $sesion->set('itemsPerPage', $this->container->getParameter('paginator.items_per_page'));
                    }
                } else {
                    if ($itemsPerPage > 0) {
                        $sesion->set('itemsPerPage', $itemsPerPage);
                    }
                }
                $paginador = $this->get('ideup.simple_paginator');

                if ($itemsPerPage) {

                    $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
                } else {
                    $paginador->setItemsPerPage($sesion->get('itemsPerPage'));
                }
                $paginador->setMaxPagerItems(6);

                $peticion->setSession($sesion);
                $consulta = $em->createQuery(
                    'SELECT imps
                    FROM FacturasBundle:FacturaImp imps
                        JOIN imps.factura fa
                    WHERE fa.fecha BETWEEN :desde AND :hasta'
                );

                $fechaDesde = new \DateTime();
                $fechaDesde->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsDesde = explode('/', $form['desde']);
                $fechaDesde->setDate($fechaInsDesde[2], $fechaInsDesde[1], $fechaInsDesde[0]);
                $consulta->setParameter('desde', $fechaDesde, 'date');
                $fechaHasta = new \DateTime();
                $fechaHasta->setTimezone(new \DateTimeZone('America/Lima'));
                $fechaInsHasta = explode('/', $form['hasta']);
                $fechaHasta->setDate($fechaInsDesde[2], $fechaInsHasta[1], $fechaInsHasta[0]);
                $consulta->setParameter('hasta', $fechaHasta, 'date');
                $resImps = $paginador->paginate($consulta->getResult())->getResult();

                /*foreach($reImps as $resImp){
                    $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find(
                        $resImp->getImpuesto()->getId()
                    );
                    $resImp->SetPorcentaje($impuesto->getPorcentaje());
                    if ($resImp->getAntesImpItem()) {
                        $resImp->setTotal($importeFacturaSinImp * $impuesto->getPorcentaje() / 100);
                    } else {
                        $resImp->setTotal($importeFacturaConImp * $impuesto->getPorcentaje() / 100);
                    }
                }*/

                return $this->render(
                    'OtrosBundle:Reportes:resImps.html.twig',
                    array(
                        'dateForm' => $dateForm->createView(),
                        'resImps' => $resImps,
                        'paginador' => $paginador,
                        'itemsPerPage' => $sesion->get('itemsPerPage'),
                        'active' => $classActive
                    )
                );
            }
        }

        return $this->render(
            'OtrosBundle:Reportes:resImps.html.twig',
            array(
                'dateForm' => $dateForm->createView(),
                'active' => $classActive
            )
        );
    }

    /*AJAXSOURCE*/
    public function monedaBaseAction()
    {
        $em = $this->getDoctrine()->getManager();
        $monedaBase = $em->getRepository('OtrosBundle:AppParam')->findBy(array('nombre' => 'MONEDA_BASE'));

        return new Response(json_encode(array('monedaBase' => $monedaBase[0]->getValor())));
    }
}
