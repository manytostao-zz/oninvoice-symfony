<?php

namespace FusionClone\CotizacionesBundle\Controller;

use FusionClone\CotizacionesBundle\Entity\Cotizacion;
use FusionClone\CotizacionesBundle\Form\CotItemType;
use FusionClone\FacturasBundle\Entity\FacturaItem;
use FusionClone\FacturasBundle\Entity\FacturaRec;
use FusionClone\FacturasBundle\Form\FacturaRecType;
use FusionClone\FacturasBundle\Entity\FacturaImp;
use FusionClone\FacturasBundle\Form\FacturaType;
use FusionClone\NomencladoresBundle\Entity\NomProd;
use FusionClone\PagosBundle\Entity\Pago;
use FusionClone\PagosBundle\Form\PagoType;
use FusionClone\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use FusionClone\CotizacionesBundle\Form\CotizacionType;
use FusionClone\FacturasBundle\Entity\Factura;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $classActive = array('sup' => 'Cotizaciones', 'sub' => 'Ver');
        if (is_null($this->getRequest()->get('status'))) {
            $classActive['pill'] = 'Todos';
            $session->remove('filters');
        } else {
            $session->set('filters', array('estado' => $this->getRequest()->get('status')));
            if ($this->getRequest()->get('status') == 'Borrador') {
                $classActive['pill'] = 'Borrador';
            } elseif ($this->getRequest()->get('status') == 'Enviado') {
                $classActive['pill'] = 'Enviado';
            } elseif ($this->getRequest()->get('status') == 'Aprobado') {
                $classActive['pill'] = 'Aprobado';
            } elseif ($this->getRequest()->get('status') == 'Rechazado') {
                $classActive['pill'] = 'Rechazado';
            } elseif ($this->getRequest()->get('status') == 'Cancelado') {
                $classActive['pill'] = 'Cancelado';
            }
        }

        $peticion->setSession($session);

        return $this->render(
            'CotizacionesBundle:Default:index.html.twig',
            array(
                'active' => $classActive,
                'status' => $peticion->get('status')
            )
        );
    }

    public function createAction()
    {
        $active = array('sup' => 'Cotizaciones', 'sub' => 'Crear');
        $cotizacion = new Cotizacion();
        $formCot = $this->getInsertFormCot($cotizacion);

        return $this->render(
            'CotizacionesBundle:Default:create.html.twig',
            array('formCot' => $formCot->createView(), 'active' => $active)
        );
    }

    public function detailsAction($id)
    {
        $active = array('sup' => 'Cotizaciones', 'sub' => '');
        $cotizacion = $this->getDoctrine()->getManager()->getRepository(
            'CotizacionesBundle:Cotizacion'
        )->find($id);
        $importeFacturaSinImp = 0;
        $importeFacturaConImp = 0;

        /*Cargo los productos de la cotizacion*/
        /*********************************************************************************/
        if (is_null($cotizacion->getCotItems())) {
            $cotItems = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:CotItem'
            )->findByCotizacion($cotizacion->getId());
            if (count($cotItems) > 0) {
                foreach ($cotItems as $cotItem) {
                    $cotizacion->addCotItem($cotItem);
                    $impuesto = 0;
                    if (!is_null($cotItem->getImpuesto())) {
                        $impuesto = ($cotItem->getCantidad() * $cotItem->getPrecio() * $cotItem->getImpuesto(
                                )->getPorcentaje()) / 100;
                    }
                    $cotItem->setTotal(($cotItem->getCantidad() * $cotItem->getPrecio()) + $impuesto);
                    $importeFacturaSinImp = $importeFacturaSinImp + ($cotItem->getCantidad() * $cotItem->getPrecio());
                    $importeFacturaConImp = $importeFacturaConImp + $cotItem->getTotal();
                }
            }
        }
        /*********************************************************************************/
        /*Cargo los impuestos de la cotizacion*/
        /*********************************************************************************/
        if (is_null($cotizacion->getCotImps())) {
            $cotImps = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:CotImp'
            )->findByCotizacion($cotizacion->getId());
            if (count($cotImps) > 0) {
                foreach ($cotImps as $cotImp) {
                    $cotizacion->addCotImp($cotImp);
                    $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find(
                        $cotImp->getImpuesto()->getId()
                    );
                    $cotImp->SetPorcentaje($impuesto->getPorcentaje());
                    if ($cotImp->getAntesImpItem()) {
                        $cotImp->setTotal($importeFacturaSinImp * $impuesto->getPorcentaje() / 100);
                    } else {
                        $cotImp->setTotal($importeFacturaConImp * $impuesto->getPorcentaje() / 100);
                    }
                }
            }
        }
        /*********************************************************************************/

        $usuario = $cotizacion->getUsuario();
        $cotizacionType = new CotizacionType();
        $cotizacionType->setAction($this->generateUrl('cotizaciones_save'));
        $cotizacionType->setTipoForm('edit');
        $cotizacionType->setId($id);

        if ($cotizacion->getEstado() == 'Borrador') {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT esta FROM NomencladoresBundle:NomEsta esta JOIN esta.tdoc tdoc
                WHERE esta.tdoc = tdoc.id AND tdoc.codigo = :codigo AND esta.descripcion NOT IN (:estados)'
            );
            $consulta->setParameter('estados', array('Aprobado', 'Rechazado'));
            $consulta->setParameter('codigo', 'COT');
            $estados = $consulta->getResult();
        } else {
            $estados = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomEsta'
            )->findByTdoc($cotizacion->getTdocConf()->getTdoc()->getId());
        }
        $estadosChoices = new ChoiceList($estados, $estados);
        $cotizacionType->setEstadosChoices($estadosChoices);

        $monedas = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomMone'
        )->findAll();
        $monedasChoices = new ChoiceList($monedas, $monedas);
        $cotizacionType->setMonedasChoices($monedasChoices);

        $formCot = $this->createForm($cotizacionType, $cotizacion);

        $factura = new Factura();
        $formFactura = $this->getInsertFormFact($factura, $cotizacion);

        return $this->render(
            'CotizacionesBundle:Default:details.html.twig',
            array(
                'cotizacion' => $cotizacion,
                'formCot' => $formCot->createView(),
                'formFactura' => $formFactura->createView(),
                'usuario' => $usuario,
                'active' => $active
            )
        );
    }

    public function cancelAction($id)
    {
        $cotizacion = $this->getDoctrine()->getManager()->getRepository(
            'CotizacionesBundle:Cotizacion'
        )->find($id);

        $em = $this->getDoctrine()->getManager();

        $cotizacion->setEstado(
            $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus(
                'COT',
                'Cancelado'
            )
        );
        $em->persist($cotizacion);
        $em->flush();


        return $this->detailsAction($id);
    }

    public function approveAction($id)
    {
        $cotizacion = $this->getDoctrine()->getManager()->getRepository(
            'CotizacionesBundle:Cotizacion'
        )->find($id);

        $em = $this->getDoctrine()->getManager();

        $cotizacion->setEstado(
            $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus(
                'COT',
                'Aprobado'
            )
        );
        $em->persist($cotizacion);
        $em->flush();


        return $this->detailsAction($id);
    }

    public function rejectAction($id)
    {
        $cotizacion = $this->getDoctrine()->getManager()->getRepository(
            'CotizacionesBundle:Cotizacion'
        )->find($id);

        $em = $this->getDoctrine()->getManager();

        $cotizacion->setEstado(
            $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus(
                'COT',
                'Rechazado'
            )
        );
        $em->persist($cotizacion);
        $em->flush();


        return $this->detailsAction($id);
    }

    public function generateAction()
    {
        $peticion = $this->getRequest();
        if ($peticion->get('form')) {
            $form = $peticion->get('form');
        } else {
            $form = $peticion->get('formFactura');
        }
        $cotizacion = $this->getDoctrine()->getManager()->getRepository('CotizacionesBundle:Cotizacion')->find(
            $form['cotizacion']
        );
        if (is_null($cotizacion->getCotItems())) {
            $cotItems = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:CotItem'
            )->findByCotizacion($form['cotizacion']);
            foreach ($cotItems as $cotItem) {
                $cotizacion->addCotItem($cotItem);
            }
        }
        if (is_null($cotizacion->getCotImps())) {
            $cotImps = $this->getDoctrine()->getManager()->getRepository('CotizacionesBundle:CotImp')->findByCotizacion(
                $form['cotizacion']
            );
            foreach ($cotImps as $cotImp) {
                $cotizacion->addCotImp($cotImp);
            }
        }
        $factura = new Factura();

        $formFactura = $this->getInsertFormFact($factura, $cotizacion);
        $formFactura->handleRequest($peticion);
        if ($formFactura->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /*Valores por defecto de la Factura*/
            $factura->setFechaVenc(new \DateTime());
            $factura->getFechaVenc()->setTimeStamp($factura->getFecha()->getTimeStamp() + (3600 * 24));
            $tdocConf = $factura->getTdocConf();
            $margen = '';

            /*Preparando y asignando el codigo de la Factura*/

            for ($i = 0; $i < $tdocConf->getCantDigCons() - strlen(
                $factura->getTdocConf()->getConsecutivo() + 1
            ); $i = $i + 1) {
                $margen = $margen . '0';

            }
            $fecha = getdate();
            $codigo = $factura->getTdocConf()->getPrefijo();
            if ($factura->getTdocConf()->getAnno()) {
                $codigo = $codigo . $fecha['year'];
            }
            if ($factura->getTdocConf()->getMes()) {
                $codigo = $codigo . $fecha['mon'];
            }
            $codigo = $codigo . $margen . $factura->getTdocConf()->getConsecutivo();
            $factura->setCodigo($codigo);

            $estado = $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus('FAC', 'Borrador');
            $factura->setEstado($estado);
            $factura->setImporte($cotizacion->getImporte());
            $factura->setSaldo(0.00);
            $factura->setMoneda($cotizacion->getMoneda());
            $factura->setTasa($cotizacion->getMoneda()->getTasa());
            $factura->setPie($cotizacion->getPie());
            $factura->setTerms($cotizacion->getTerms());
            $em->persist($factura);

            /*Actualizo el consecutivo de la Configuracion del Tipo de Documento*/
            $cons = $em->getRepository('NomencladoresBundle:NomTdocConf')->find($factura->getTdocConf()->getId());
            $cons->setConsecutivo($cons->getConsecutivo() + 1);

            $em->persist($cons);
            $em->flush();

            $importeFacturaSinImp = 0;
            $importeFacturaConImp = 0;
            $itemSubtotalSinImp = 0;
            $itemSubtotalConImp = 0;
            $impuesto = 0;
            if (count($cotizacion->getCotItems()) > 0) {
                foreach ($cotizacion->getCotItems() as $cotItem) {
                    $factItem = new FacturaItem();
                    $factItem->setFactura($factura->getId());
                    $factItem->setNombre($cotItem->getNombre());
                    $factItem->setDescripcion($cotItem->getDescripcion());
                    $factItem->setPrecio($cotItem->getPrecio());
                    $factItem->setCantidad($cotItem->getCantidad());
                    $factItem->setImpuesto($cotItem->getImpuesto());
                    $factItem->setProducto($cotItem->getProducto());
                    $factItem->setTotal($cotItem->getTotal());
                    $factura->addFactItem($factItem);
                }
            }
            if (count($factura->getFactItems()) > 0) {
                foreach ($factura->getFactItems() as $factItem) {
                    $impuesto = 0;
                    if (!is_null($factItem->getImpuesto())) {
                        $impuesto = ($factItem->getCantidad() * $factItem->getPrecio() * $factItem->getImpuesto(
                                )->getPorcentaje()) / 100;
                    }
                    $factItem->setTotal(($factItem->getCantidad() * $factItem->getPrecio()) + $impuesto);
                    $importeFacturaSinImp = $importeFacturaSinImp + ($factItem->getCantidad() * $factItem->getPrecio());
                    $importeFacturaConImp = $importeFacturaConImp + $factItem->getTotal();
                }
            }
            if (count($factura->getFactImps()) > 0) {
                foreach ($cotizacion->getCotImps() as $cotImp) {
                    $factImp = new FacturaImp();
                    $factImp->setFactura($factura->getId());
                    $factImp->setTotal($cotImp->getTotal());
                    $factImp->setImpuesto($cotImp->getImpuesto());
                    $factImp->setAntesImpItem($cotImp->getAntesImpItem());
                    $factImp->setPorcentaje($cotImp->getPorcentaje());
                    $factura->addFactImp($factImp);
                }
            }
            if (count($factura->getFactImps()) > 0) {
                foreach ($factura->getFactImps() as $factImp) {
                    $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find(
                        $factImp->getImpuesto()->getId()
                    );
                    $factImp->SetPorcentaje($impuesto->getPorcentaje());
                    if ($factImp->getAntesImpItem()) {
                        $factImp->setTotal($importeFacturaSinImp * $impuesto->getPorcentaje() / 100);
                    } else {
                        $factImp->setTotal($importeFacturaConImp * $impuesto->getPorcentaje() / 100);
                    }
                }
            }
            $factItems = $factura->getFactItems();
            if (count($factItems) > 0) {
                foreach ($factItems as $factItem) {
                    if ($factItem->getId() > 0) {
                        $newFactItem = $factItem;
                        $factItem = $this->getDoctrine()->getManager()->getRepository(
                            'FacturasBundle:FacturaItem'
                        )->find($factItem->getId());
                        if (is_null($newFactItem->getProducto()) and is_null($newFactItem->getNombre()) and is_null(
                                $newFactItem->getDescripcion()
                            ) and is_null($newFactItem->getCantidad()) and is_null($newFactItem->getPrecio())
                        ) {
                            $factura->removeFactItem($newFactItem);
                            if (!is_null($factItem)) {
                                $this->getDoctrine()->getManager()->remove($factItem);
                            }
                            break;
                        }
                        $factItem->setFactura($newFactItem->getFactura());
                        $factItem->setCantidad($newFactItem->getCantidad());
                        $factItem->setImpuesto($newFactItem->getImpuesto());
                        $factItem->setProducto($newFactItem->getProducto());
                        $factItem->setPrecio($newFactItem->getPrecio());
                        $itemSubtotalSinImp = $itemSubtotalSinImp + ($factItem->getCantidad() * $factItem->getPrecio());
                        $itemSubtotalConImp = $itemSubtotalConImp +
                            (($factItem->getCantidad() * $factItem->getPrecio()) +
                                (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad() * $factItem->getPrecio(
                                    ) * $factItem->getImpuesto()->getPorcentaje() / 100) : 0));
                    }
                    if (!is_null($factItem->getNombre())) {
                        $producto = $this->getDoctrine()->getManager()->getRepository(
                            'NomencladoresBundle:NomProd'
                        )->findByNombre($factItem->getNombre());
                        if (count($producto) <= 0) {
                            $producto = new NomProd();
                            $producto->setNombre($factItem->getNombre());
                            $producto->setPrecio($factItem->getPrecio());
                            $producto->setDescripcion($factItem->getDescripcion());
                            $factItem->setProducto($producto);
                        } else {
                            $factItem->setProducto($producto[0]);
                            if (is_null($factItem->getDescripcion())) {
                                $factItem->setDescripcion($factItem->getProducto()->getDescripcion());
                            }
                        }
                        if ($factItem->getId() <= 0) {
                            $itemSubtotalSinImp = $itemSubtotalSinImp + ($factItem->getCantidad(
                                    ) * $factItem->getPrecio());
                            $itemSubtotalConImp = $itemSubtotalConImp +
                                (($factItem->getCantidad() * $factItem->getPrecio()) +
                                    (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad(
                                        ) * $factItem->getPrecio() * $factItem->getImpuesto()->getPorcentaje(
                                        ) / 100) : 0));
                        }
                        $em->persist($factItem);
                    } else {
                        $factura->removeFactItem($factItem);
                    }
                }

            }
            /**********************************************************************************************/
            /*Procesamiento de los impuestos asociados a la factura*/
            $factImps = $factura->getFactImps();
            $impuesto = 0;
            if (count($factImps) > 0) {
                foreach ($factImps as $factImp) {
                    if ($factImp->getId() > 0) {
                        $newFactImp = $factImp;
                        $factImp = $em->getRepository('FacturasBundle:FacturaImp')->find($factImp->getId());
                        $factImp->setImpuesto($newFactImp->getImpuesto());
                        if (!is_null($newFactImp->getImpuesto())) {
                            $factImp->setAntesImpItem($newFactImp->getAntesImpItem());
                            if ($factImp->getAntesImpItem()) {
                                $impuesto = $impuesto + ($itemSubtotalSinImp * $newFactImp->getImpuesto(
                                        )->getPorcentaje() / 100);
                            } else {
                                $impuesto = $impuesto + ($itemSubtotalConImp * $newFactImp->getImpuesto(
                                        )->getPorcentaje() / 100);
                            }
                            $em->persist($factImp);
                        } else {
                            $em->remove($factImp);
                            $factura->removeFactImp($newFactImp);
                        }
                    } elseif (!is_null($factImp->getImpuesto())) {
                        if ($factImp->getAntesImpItem()) {
                            $impuesto = $impuesto + ($itemSubtotalSinImp * $factImp->getImpuesto()->getPorcentaje(
                                    ) / 100);
                        } else {
                            $impuesto = $impuesto + ($itemSubtotalConImp * $factImp->getImpuesto()->getPorcentaje(
                                    ) / 100);
                        }
                        $em->persist($factImp);
                    } else {
                        $factura->removeFactImp($factImp);

                    }
                }
            }

            $factura->setImporte($itemSubtotalConImp + $impuesto);

            /*Cargo los pagos asociados a la factura*/
            /*********************************************************************************/
            $pagos = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')->findByFactura(
                $factura->getId()
            );
            $pagado = 0;
            foreach ($pagos as $pago) {
                $pagado = $pagado + $pago->getImporte();
            }
            /*********************************************************************************/
            $factura->setSaldo($factura->getImporte() - $pagado);
            $em->persist($factura);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_add',
                'Factura creada exitosamente'
            );

            $pago = new Pago();
            $pago->setFactura($factura);

            $pagoType = new PagoType();
            $pagoType->setTipoForm('add');
            $pagoType->setAction($this->generateUrl('pagos_save'));
            $formPago = $this->createForm($pagoType, $pago);

            $usuario = $this->get('security.context')->getToken()->getUser();

            $facturaType = new FacturaType();
            $facturaType->setAction($this->generateUrl('facturas_save'));
            $facturaType->setTipoForm('edit');
            $facturaType->setId($factura->getId());

            $estados = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomEsta'
            )->findByTdoc($factura->getTdocConf()->getTdoc()->getId());
            $estadosChoices = new ChoiceList($estados, $estados);
            $facturaType->setEstadosChoices($estadosChoices);

            $monedas = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomMone'
            )->findAll();
            $monedasChoices = new ChoiceList($monedas, $monedas);
            $facturaType->setMonedasChoices($monedasChoices);

            $formFactura = $this->createForm($facturaType, $factura);
            $recType = new FacturaRecType();
            $recurrencia = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaRec'
            )->findByFacBase($factura->getId());
            if (!isset($recurrencia[0])) {
                $recurrencia[0] = new FacturaRec();
                $recType->setTipoForm('add');
            } else {
                $recType->setTipoForm('edit');
            }
            $recType->setFacBase($factura->getId());
            $recType->setAction($this->generateUrl('facturas_recurrence'));
            $formRec = $this->createForm($recType, $recurrencia[0]);

            return $this->render(
                'FacturasBundle:Default:details.html.twig',
                array(
                    'factura' => $factura,
                    'formFactura' => $formFactura->createView(),
                    'usuario' => $usuario,
                    'formPago' => $formPago->createView(),
                    'recurrencia' => $recurrencia,
                    'formRec' => $formRec->createView()
                )
            );
        }

    }

    public function saveAction()
    {
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $active = array('sup' => 'Cotizaciones', 'sub' => 'Crear');
        if ($peticion->get('form')) {
            $form = $peticion->get('form');
        } else {
            $form = $peticion->get('formCot');
        }
        if ($form['tipoForm'] == 'add') {
            $cotizacion = new Cotizacion();
            $formCot = $this->getInsertFormCot($cotizacion)->handleRequest($peticion);
            if ($formCot->isValid()) {
                $em = $this->getDoctrine()->getManager();

                /*Valores por defecto de la Cotizacion*/
                $cotizacion->setFechaVenc(new \DateTime());
                $cotizacion->getFechaVenc()->setTimeStamp($cotizacion->getFecha()->getTimeStamp() + (3600 * 24));
                $tdocConf = $cotizacion->getTdocConf();
                $margen = '';

                /*Preparando y asignando el codigo de la Cotizacion*/

                for ($i = 0; $i < $tdocConf->getCantDigCons() - strlen(
                    $cotizacion->getTdocConf()->getConsecutivo() + 1
                ); $i = $i + 1) {
                    $margen = $margen . '0';

                }
                $fecha = getdate();
                $codigo = $cotizacion->getTdocConf()->getPrefijo();
                if ($cotizacion->getTdocConf()->getAnno()) {
                    $codigo = $codigo . $fecha['year'];
                }
                if ($cotizacion->getTdocConf()->getMes()) {
                    $codigo = $codigo . $fecha['mon'];
                }
                $codigo = $codigo . $margen . $cotizacion->getTdocConf()->getConsecutivo();
                $cotizacion->setCodigo($codigo);

                $estado = $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus('COT', 'Borrador');
                $cotizacion->setEstado($estado);
                $cotizacion->setImporte(0.00);
                $cotizacion->setMoneda($cotizacion->getCliente()->getDefMone());
                $cotizacion->setTasa($cotizacion->getCliente()->getDefMone()->getTasa());
                $em->persist($cotizacion);

                /*Actualizo el consecutivo de la Configuracion del Tipo de Documento*/
                $cons = $em->getRepository('NomencladoresBundle:NomTdocConf')->find(
                    $cotizacion->getTdocConf()->getId()
                );
                $cons->setConsecutivo($cons->getConsecutivo() + 1);

                $em->persist($cons);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Cotización creada exitosamente'
                );

                return $this->detailsAction($cotizacion->getId());
            }

            return $this->render(
                'CotizacionesBundle:Default:create.html.twig',
                array('formCot' => $formCot->createView(), 'active' => $active)
            );

        } elseif ($form['tipoForm'] == 'edit') {
            $cotizacion = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:Cotizacion'
            )->find($form['id']);
            $cotizacionType = new CotizacionType();
            $cotizacionType->setAction($this->generateUrl('cotizaciones_save'));
            $cotizacionType->setTipoForm('edit');

            $estados = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomEsta'
            )->findByTdoc($cotizacion->getTdocConf()->getTdoc()->getId());
            $estadosChoices = new ChoiceList($estados, $estados);
            $cotizacionType->setEstadosChoices($estadosChoices);

            $monedas = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomMone'
            )->findAll();
            $monedasChoices = new ChoiceList($monedas, $monedas);
            $cotizacionType->setMonedasChoices($monedasChoices);

            $formCot = $this->createForm($cotizacionType, $cotizacion);

            $formCot->handleRequest($this->getRequest());
            if ($formCot->isValid()) {
                $itemSubtotalSinImp = 0;
                $itemSubtotalConImp = 0;
                $impuesto = 0;

                /* Procesamiento de productos asociados a la factura*/
                /**********************************************************************************************/
                $cotItems = $cotizacion->getCotItems();
                if (count($cotItems) > 0) {
                    foreach ($cotItems as $cotItem) {
                        /*
                         * Si ya existe la linea de la cotización, actualizo los valores
                         * con los que traigo desde la vista.
                         */
                        if (!is_null($cotItem->getId()) and $cotItem->getId() > 0) {
                            $newCotItem = $cotItem;
                            $cotItem = $this->getDoctrine()->getManager()->getRepository(
                                'CotizacionesBundle:CotItem'
                            )->find($cotItem->getId());
                            /*
                             * Si está vacío el nuevo item, lo elimino
                             * */
                            if (is_null($newCotItem->getProducto())) {
                                $formCot->addError(new FormError("El campo del Producto no puede quedar vacío"));
                                $usuario = $this->get('security.context')->getToken()->getUser();

                                $factura = new Factura();
                                $formFactura = $this->getInsertFormFact($factura, $cotizacion);

                                return $this->render(
                                    'CotizacionesBundle:Default:details.html.twig',
                                    array(
                                        'cotizacion' => $cotizacion,
                                        'formCot' => $formCot->createView(),
                                        'usuario' => $usuario,
                                        'formFactura' => $formFactura->createView()
                                    )
                                );
                            }
                            if ((is_null($newCotItem->getCantidad())
                                and is_null($newCotItem->getDescripcion())
                                and is_null($newCotItem->getPrecio()))
                            ) {
                                $cotizacion->removeCotItem($newCotItem);
                                if (!is_null($cotItem)) {
                                    $this->getDoctrine()->getManager()->remove($cotItem);
                                }
                                break;
                            }
                            /*
                             * Actualizo el item con los nuevos datos
                             * */
                            $cotItem->setCotizacion($newCotItem->getCotizacion());
                            $cotItem->setCantidad($newCotItem->getCantidad());
                            $cotItem->setImpuesto($newCotItem->getImpuesto());
                            $cotItem->setProducto($newCotItem->getProducto());
                            $cotItem->setDescripcion($newCotItem->getDescripcion());
                            $cotItem->setPrecio($newCotItem->getPrecio());
                            if (!is_null($cotItem->getProducto())) {
                                foreach ($form['cotItems'] as $item) {
                                    if (array_key_exists(
                                            'producto',
                                            $item
                                        ) and $item['producto'] == $cotItem->getProducto()->getId()
                                    ) {
                                        $cotItem->getProducto()->setDescripcion($item['descripcion']);
                                    }
                                }
                            }
                            $cotItem->setTotal(
                                ($cotItem->getCantidad() * $cotItem->getPrecio()) +
                                (!is_null($cotItem->getImpuesto()) ? ($cotItem->getCantidad() * $cotItem->getPrecio(
                                    ) * $cotItem->getImpuesto()->getPorcentaje() / 100) : 0)
                            );
                            $itemSubtotalSinImp = $itemSubtotalSinImp + ($cotItem->getCantidad() * $cotItem->getPrecio(
                                    ));
                            $itemSubtotalConImp = $itemSubtotalConImp +
                                (($cotItem->getCantidad() * $cotItem->getPrecio()) +
                                    (!is_null($cotItem->getImpuesto()) ? ($cotItem->getCantidad() * $cotItem->getPrecio(
                                        ) * $cotItem->getImpuesto()->getPorcentaje() / 100) : 0));
                        } else {
                            if (is_null($cotItem->getProducto())) {
                                $cotizacion->removeCotItem($cotItem);
                                if (!is_null($cotItem)) {
                                    $this->getDoctrine()->getManager()->remove($cotItem);
                                }
                                break;
                            } else {
                                foreach ($form['cotItems'] as $item) {
                                    if (array_key_exists(
                                            'producto',
                                            $item
                                        ) and $item['producto'] == $cotItem->getProducto()->getId()
                                    ) {
                                        $cotItem->getProducto()->setDescripcion($item['descripcion']);
                                    }
                                }
                                $cotItem->setTotal(
                                    ($cotItem->getCantidad() * $cotItem->getPrecio()) +
                                    (!is_null($cotItem->getImpuesto()) ? ($cotItem->getCantidad() * $cotItem->getPrecio(
                                        ) * $cotItem->getImpuesto()->getPorcentaje() / 100) : 0)
                                );
                                $itemSubtotalSinImp = $itemSubtotalSinImp + ($cotItem->getCantidad(
                                        ) * $cotItem->getPrecio());
                                $itemSubtotalConImp = $itemSubtotalConImp +
                                    (($cotItem->getCantidad() * $cotItem->getPrecio()) +
                                        (!is_null($cotItem->getImpuesto()) ? ($cotItem->getCantidad(
                                            ) * $cotItem->getPrecio() * $cotItem->getImpuesto()->getPorcentaje(
                                            ) / 100) : 0));
                                $em->persist($cotItem);
                            }
                        }
                    }
                }
                /**********************************************************************************************/
                /*Procesamiento de los impuestos asociados a la factura*/
                $cotImps = $cotizacion->getCotImps();
                if (count($cotImps) > 0) {
                    foreach ($cotImps as $cotImp) {
                        if ($cotImp->getId() > 0) {
                            $newCotImp = $cotImp;
                            $cotImp = $em->getRepository('CotizacionesBundle:CotImp')->find($cotImp->getId());
                            $cotImp->setImpuesto($newCotImp->getImpuesto());
                            if (!is_null($newCotImp->getImpuesto())) {
                                $cotImp->setAntesImpItem($newCotImp->getAntesImpItem());
                                if ($cotImp->getAntesImpItem()) {
                                    $impuesto = $impuesto + ($itemSubtotalSinImp * $newCotImp->getImpuesto(
                                            )->getPorcentaje() / 100);
                                } else {
                                    $impuesto = $impuesto + ($itemSubtotalConImp * $newCotImp->getImpuesto(
                                            )->getPorcentaje() / 100);
                                }
                                $em->persist($cotImp);
                            } else {
                                $em->remove($cotImp);
                                $cotizacion->removeCotImp($newCotImp);
                            }
                        } elseif (!is_null($cotImp->getImpuesto())) {
                            if ($cotImp->getAntesImpItem()) {
                                $impuesto = $impuesto + ($itemSubtotalSinImp * $cotImp->getImpuesto()->getPorcentaje(
                                        ) / 100);
                            } else {
                                $impuesto = $impuesto + ($itemSubtotalConImp * $cotImp->getImpuesto()->getPorcentaje(
                                        ) / 100);
                            }
                            $em->persist($cotImp);
                        } else {
                            $cotizacion->removeCotImp($cotImp);

                        }
                    }
                }
                $cotizacion->setImporte($itemSubtotalConImp + $impuesto);

                /**********************************************************************************************/
                $usuario = null;
                if ($this->get('security.context')->isGranted('ROLE_USUARIO')) {
                    $usuario = $this->get('security.context')->getToken()->getUser();
                }
                $cotizacion->setUsuario($usuario);
                $em->persist($cotizacion);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Cotización actualizada exitosamente'
                );

                if ($cotizacion->getEstado() == 'Enviado') {
                    return $this->sendMailAction($cotizacion->getId());
                } else {
                    return $this->detailsAction($cotizacion->getId());
                }
            }

            $usuario = $this->get('security.context')->getToken()->getUser();

            $factura = new Factura();
            $formFactura = $this->getInsertFormFact($factura, $cotizacion);

            return $this->render(
                'CotizacionesBundle:Default:details.html.twig',
                array(
                    'cotizacion' => $cotizacion,
                    'formCot' => $formCot->createView(),
                    'usuario' => $usuario,
                    'formFactura' => $formFactura->createView()
                )
            );
        }

    }

    public
    function deleteAction(
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $cotizacion = $em->find('CotizacionesBundle:Cotizacion', $id);
        if (!$cotizacion) {
            $this->createNotFoundException('No se encontró la cotización.');
        } else {
            $cotItems = $em->getRepository('CotizacionesBundle:CotItem')->findByCotizacion($id);
            $cotImps = $em->getRepository('CotizacionesBundle:CotImp')->findByCotizacion($id);
            foreach ($cotItems as $cotItem) {
                $em->remove($cotItem);
            }
            foreach ($cotImps as $cotImp) {
                $em->remove($cotImp);
            }

            $em->remove($cotizacion);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Cotización eliminada exitosamente'
            );

            return $this->redirect($this->generateUrl('cotizaciones_homepage'));
        }
    }

    public
    function previewAction(
        $id,
        $preview
    ) {
        $classActive = array('sup' => 'Cotizaciones', 'sub' => '');
        $em = $this->getDoctrine()->getManager();
        $cotizacion = $em->getRepository('CotizacionesBundle:Cotizacion')->find($id);
        $importeCotSinImp = 0;
        $importeCotConImp = 0;

        /*Cargo los productos de la factura*/
        /*********************************************************************************/
        if (is_null($cotizacion->getCotItems())) {
            $cotItems = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:CotItem'
            )->findByCotizacion($cotizacion->getId());
            if (count($cotItems) > 0) {
                foreach ($cotItems as $cotItem) {
                    $cotizacion->addCotItem($cotItem);
                }
            }
        }
        if (count($cotizacion->getCotItems()) > 0) {
            foreach ($cotizacion->getCotItems() as $cotItem) {
                $impuesto = 0;
                if (!is_null($cotItem->getImpuesto())) {
                    $impuesto = ($cotItem->getCantidad() * $cotItem->getPrecio() * $cotItem->getImpuesto(
                            )->getPorcentaje()) / 100;
                }
                $cotItem->setTotal(($cotItem->getCantidad() * $cotItem->getPrecio()) + $impuesto);
                $importeCotSinImp = $importeCotSinImp + ($cotItem->getCantidad() * $cotItem->getPrecio());
                $importeCotConImp = $importeCotConImp + $cotItem->getTotal();
            }
        }
        /*********************************************************************************/
        /*Cargo los impuestos de la factura*/
        /*********************************************************************************/
        if (is_null($cotizacion->getCotImps())) {
            $cotImps = $this->getDoctrine()->getManager()->getRepository(
                'CotizacionesBundle:CotImp'
            )->findByCotizacion($cotizacion->getId());
            if (count($cotImps) > 0) {
                foreach ($cotImps as $cotImp) {
                    $cotizacion->addCotImp($cotImp);
                }
            }
        }
        if (count($cotizacion->getCotImps()) > 0) {
            foreach ($cotizacion->getCotImps() as $cotImp) {
                $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find(
                    $cotImp->getImpuesto()->getId()
                );
                $cotImp->SetPorcentaje($impuesto->getPorcentaje());
                if ($cotImp->getAntesImpItem()) {
                    $cotImp->setTotal($importeCotSinImp * $impuesto->getPorcentaje() / 100);
                } else {
                    $cotImp->setTotal($importeCotConImp * $impuesto->getPorcentaje() / 100);
                }
            }
        }
        /*********************************************************************************/

        $usuario = $this->get('security.context')->getToken()->getUser();

        if ($preview == '1') {
            return $this->render(
                'CotizacionesBundle:Default:preview.html.twig',
                array(
                    'cotizacion' => $cotizacion,
                    'usuario' => $usuario,
                    'preview' => $preview,
                    'active' => $classActive
                )
            );
        } elseif ($preview == '0') {
            return $this->render(
                'CotizacionesBundle:Default:pdfFile.html.twig',
                array(
                    'cotizacion' => $cotizacion,
                    'usuario' => $usuario,
                    'preview' => $preview,
                    'active' => $classActive
                )
            );
        }

    }

    public
    function sendMailAction(
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $cotizacion = $em->getRepository('CotizacionesBundle:Cotizacion')->find($id);
        $cuerpo = $this->previewAction($id, '0');
        $mensaje = \Swift_Message::newInstance()
            ->setSubject('Cotizacion ' . $cotizacion->getCodigo())
            ->setCc('ventas@conexionporsatelite.com')
            ->setFrom($cotizacion->getUsuario()->getEmail())
            ->setTo($cotizacion->getCliente()->getEmail())
            ->setBody($cuerpo, 'text/html');
        try {
            $this->container->get('mailer')->send($mensaje);

            $this->get('session')->getFlashBag()->add(
                'info_edit',
                'Email enviado exitosamente a ' . $cotizacion->getCliente()->getEmail()
            );
        } catch (\Swift_TransportException $e) {
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                $e->getMessage()
            );
        }

        return $this->detailsAction($id);
    }

    public
    function pdfAction(
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $pdfObj = $this->get("white_october.tcpdf")->create();
        $pdfObj->SetAuthor('Kepnix Capital E.I.R.L');
        $pdfObj->SetTitle('Cotización ' . $em->getRepository('CotizacionesBundle:Cotizacion')->find($id)->getCodigo());
        $pdfObj->SetSubject('Cotización');
        $pdfObj->SetKeywords('Cotización');
        $pdfObj->setFontSubsetting(true);
        $pdfObj->SetFont('dejavusans', '', 10, '', true);
        $pdfObj->AddPage();
        $pdfObj->SetMargins(0, 0, 5);
        $pdfObj->SetCellPadding(2);
        $html = $this->previewAction($id, '0');
        $html->headers = null;
        $html->setStatusCode(200, 'Servicio de exportación a PDF.');
        $html->setProtocolVersion('');
        $pdfObj->writeHTMLCell(
            $w = 0,
            $h = 0,
            $x = '',
            $y = '',
            $html,
            $border = 0,
            $ln = 0,
            $fill = 0,
            $reseth = true,
            $align = '',
            $autopadding = false
        );

        return new Response(
            $pdfObj->Output(),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="cotizacion.pdf"'
            )
        );
    }

    public
    function getInsertFormCot(
        $cotizacion
    ) {
        $usuario = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!is_null($usuario->getCliente())) {
            $clientes = $em->getRepository('ClientesBundle:Cliente')->findBy(
                array('activo' => 1, 'id' => $usuario->getCliente())
            );
        } else {
            $clientes = $em->getRepository('ClientesBundle:Cliente')->findBy(
                array('activo' => 1)
            );
        }
        $clientes = new ChoiceList($clientes, $clientes);
        $tdocConf = $em->getRepository('NomencladoresBundle:NomTdocConf')->findByTdocCod('COT');
        $tdocConf = new ChoiceList($tdocConf, $tdocConf);
        $form = $this->createFormBuilder($cotizacion)
            ->setAttribute('name', 'formCot')
            ->setAttribute('id', 'formCot')
            ->setAction($this->generateUrl('cotizaciones_save'))
            ->add('tipoForm', 'hidden', array('mapped' => false, 'data' => 'add'))
            ->add(
                'cliente',
                'choice',
                array('choice_list' => $clientes, 'attr' => array('class' => 'form-control'))
            )
            ->add(
                'fecha',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'data' => new \DateTime('now'),
                    'invalid_message' => 'Este valor de fecha no es válido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'tdocConf',
                'choice',
                array('choice_list' => $tdocConf, 'attr' => array('class' => 'form-control'))
            )
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $form;
    }

    public
    function getInsertFormFact(
        $factura,
        $cotizacion
    ) {
        $em = $this->getDoctrine()->getManager();
        $clientes = $em->getRepository('ClientesBundle:Cliente')->findBy(
            array('activo' => 1, 'id' => $cotizacion->getCliente()->getId())
        );
        $clientes = new ChoiceList($clientes, $clientes);
        $tdocConf = $em->getRepository('NomencladoresBundle:NomTdocConf')->findByTdocCod('FAC');
        $tdocConf = new ChoiceList($tdocConf, $tdocConf);
        $form = $this->createFormBuilder($factura)
            ->setAttribute('name', 'formFactura')
            ->setAttribute('id', 'formFactura')
            ->setAction($this->generateUrl('cotizaciones_generate'))
            ->add('tipoForm', 'hidden', array('mapped' => false, 'data' => 'add'))
            ->add('cotizacion', 'hidden', array('mapped' => false, 'data' => $cotizacion->getId()))
            ->add(
                'cliente',
                'choice',
                array('choice_list' => $clientes, 'attr' => array('class' => 'form-control'))
            )
            ->add(
                'fecha',
                'date',
                array(
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'data' => new \DateTime('now'),
                    'invalid_message' => 'Este valor de fecha no es válido',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'tdocConf',
                'choice',
                array('choice_list' => $tdocConf, 'attr' => array('class' => 'form-control'))
            )
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $form;
    }

    /*AJAX SOURCE*/

    public
    function listAction(
        Request $request
    ) {
        $get = $request->request->all();
        $session = $request->getSession();
        $filters = $session->get('filters') != null ? $filters = $session->get('filters') : array();

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('id', 'estado', 'codigo', 'fechaVenc', 'cliente', 'importe');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('CotizacionesBundle:Cotizacion')->ajaxTable($get, $filters, true)->getArrayResult(
        );

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('CotizacionesBundle:Cotizacion')->getCount());
        $query = $em->getRepository('CotizacionesBundle:Cotizacion')->ajaxTable($get, $filters, true);
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
                    case "cliente":
                        $row[] = '<a href="' . $this->generateUrl(
                                'clientes_details',
                                array('id' => $aRow[$columns[$i]]['id'])
                            )
                            . '">' . $aRow[$columns[$i]]['nombre'] . '</a>';
                        break;
                    case "estado":
                        switch ($aRow[$columns[$i]]['descripcion']) {
                            case "Borrador":
                                $row[] = '<span class="label label-draft">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                            case "Enviado":
                                $row[] = '<span class="label label-sent">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                            case "Rechazado":
                                $row[] = '<span class="label label-rejected">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                            case "Aprobado":
                                $row[] = '<span class="label label-approved">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                            case "Cancelado":
                                $row[] = '<span class="label label-canceled">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                        }

                        break;
                    case "fechaVenc":
                        if (is_null($aRow[$columns[$i]])) {
                            $row[] = '';
                        } else {
                            $date = getdate($aRow[$columns[$i]]->getTimestamp());
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
                    case "importe":
                        $importe = number_format(
                            $aRow[$columns[$i]],
                            3,
                            $aRow['moneda']['signDecimal'],
                            $aRow['moneda']['signMillares']
                        );
                        if ($aRow['moneda']['ubicaSimbol']) {
                            $row[] = $aRow['moneda']['simbolo'] . $importe;
                        } else {
                            $row[] = $importe . $aRow['moneda']['simbolo'];
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

