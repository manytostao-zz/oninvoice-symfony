<?php

namespace FusionClone\FacturasBundle\Controller;

use FusionClone\FacturasBundle\Entity\Factura;
use FusionClone\FacturasBundle\Entity\FacturaRec;
use FusionClone\FacturasBundle\Form\FacturaRecType;
use FusionClone\FacturasBundle\Form\FacturaType;
use FusionClone\NomencladoresBundle\Entity\NomProd;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use FusionClone\PagosBundle\Entity\Pago;
use FusionClone\PagosBundle\Form\PagoType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $classActive = array('sup' => 'Facturas', 'sub' => 'Ver');
        if (is_null($this->getRequest()->get('status'))) {
            $classActive['pill'] = 'Todos';
            $session->remove('filters');
        } else {
            $session->set('filters', array('estado' => $this->getRequest()->get('status')));
            if ($this->getRequest()->get('status') == 'Borrador') {
                $classActive['pill'] = 'Borrador';
            } elseif ($this->getRequest()->get('status') == 'Enviado') {
                $classActive['pill'] = 'Enviado';
            } elseif ($this->getRequest()->get('status') == 'Pagado') {
                $classActive['pill'] = 'Pagado';
            } elseif ($this->getRequest()->get('status') == 'Cancelado') {
                $classActive['pill'] = 'Cancelado';
            } elseif ($this->getRequest()->get('status') == 'Vencido') {
                $classActive['pill'] = 'Vencido';
            }
        }

        $pago = new Pago();

        $pagoType = new PagoType();
        $pagoType->setTipoForm('add');
        $pagoType->setAction($this->generateUrl('pagos_save'));
        $metodos = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->findAll();
        $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
        $formPago = $this->createForm($pagoType, $pago);


        return $this->render(
            'FacturasBundle:Default:index.html.twig',
            array(
                'active' => $classActive,
                'status' => $peticion->get('status'),
                'formPago' => $formPago->createView(),
            )
        );
    }

    public function createAction()
    {
        $active = array('sup' => 'Facturas', 'sub' => 'Crear');
        $factura = new Factura();
        $formFactura = $this->getInsertForm($factura);

        return $this->render(
            'FacturasBundle:Default:create.html.twig',
            array('formFactura' => $formFactura->createView(), 'active' => $active)
        );
    }

    public function detailsAction($id)
    {
        $classActive = array('sup' => 'Facturas', 'sub' => '');
        $factura = $this->getDoctrine()->getManager()->getRepository(
            'FacturasBundle:Factura'
        )->find($id);
        $importeFacturaSinImp = 0;
        $importeFacturaConImp = 0;

        /*Cargo los productos de la factura*/
        /*********************************************************************************/
        if (is_null($factura->getFactItems())) {
            $factItems = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaItem'
            )->findByFactura($factura->getId());
            if (count($factItems) > 0) {
                foreach ($factItems as $factItem) {
                    $factura->addFactItem($factItem);
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
        }
        /*********************************************************************************/
        /*Cargo los impuestos de la factura*/
        /*********************************************************************************/
        if (is_null($factura->getFactImps())) {
            $factImps = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaImp'
            )->findByFactura($factura->getId());
            if (count($factImps) > 0) {
                foreach ($factImps as $factImp) {
                    $factura->addFactImp($factImp);
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
        }
        /*********************************************************************************/

        $usuario = $factura->getUsuario();
        $facturaType = new FacturaType();
        $facturaType->setAction($this->generateUrl('facturas_save'));
        $facturaType->setTipoForm('edit');
        $facturaType->setId($id);

        if ($factura->getEstado() == 'Borrador') {
            $consulta = $this->getDoctrine()->getManager()->createQuery(
                'SELECT esta FROM NomencladoresBundle:NomEsta esta JOIN esta.tdoc tdoc
                WHERE esta.tdoc = tdoc.id AND tdoc.codigo = :codigo AND esta.descripcion NOT IN (:estados)'
            );
            $consulta->setParameter('estados', array('Vencido', 'Pagado'));
            $consulta->setParameter('codigo', 'FAC');
            $estados = $consulta->getResult();
        } else {
            $estados = $this->getDoctrine()->getManager()->getRepository(
                'NomencladoresBundle:NomEsta'
            )->findByTdoc($factura->getTdocConf()->getTdoc()->getId());
        }
        $estadosChoices = new ChoiceList($estados, $estados);
        $facturaType->setEstadosChoices($estadosChoices);

        $monedas = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomMone'
        )->findAll();
        $monedasChoices = new ChoiceList($monedas, $monedas);
        $facturaType->setMonedasChoices($monedasChoices);

        $formFactura = $this->createForm($facturaType, $factura);

        $pago = new Pago();
        $pago->setFactura($factura);

        $pagoType = new PagoType();
        $pagoType->setTipoForm('add');
        $pagoType->setAction($this->generateUrl('pagos_save'));
        $metodos = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->findAll();
        $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
        $formPago = $this->createForm($pagoType, $pago);

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
                'formRec' => $formRec->createView(),
                'active' => $classActive
            )
        );

    }

    public function cancelAction($id)
    {
        $factura = $this->getDoctrine()->getManager()->getRepository(
            'FacturasBundle:Factura'
        )->find($id);

        $em = $this->getDoctrine()->getManager();

        $factura->setEstado(
            $em->getRepository('NomencladoresBundle:NomEsta')->findByTdocAndStatus(
                'FAC',
                'Cancelado'
            )
        );
        $em->persist($factura);
        $em->flush();


        return $this->detailsAction($id);

    }

    public function saveAction()
    {
        $classActive = array('sup' => 'Facturas', 'sub' => '');
        $peticion = $this->getRequest();
        $active = array('sup' => 'Facturas', 'sub' => 'Crear');
        $em = $this->getDoctrine()->getManager();
        if ($peticion->get('form')) {
            $form = $peticion->get('form');
        } else {
            $form = $peticion->get('formFactura');
        }
        if ($form['tipoForm'] == 'add') {
            $factura = new Factura();
            $formFactura = $this->getInsertForm($factura)->handleRequest($peticion);
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
                $factura->setImporte(0.00);
                $factura->setSaldo(0.00);
                $factura->setMoneda($factura->getCliente()->getDefMone());
                $factura->setTasa($factura->getCliente()->getDefMone()->getTasa());
                $factura->setUsuario($this->get('security.context')->getToken()->getUser());
                $em->persist($factura);

                /*Actualizo el consecutivo de la Configuracion del Tipo de Documento*/
                $cons = $em->getRepository('NomencladoresBundle:NomTdocConf')->find($factura->getTdocConf()->getId());
                $cons->setConsecutivo($cons->getConsecutivo() + 1);

                $em->persist($cons);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Factura creada exitosamente'
                );

                return $this->detailsAction($factura->getId());
            }

            return $this->render(
                'FacturasBundle:Default:create.html.twig',
                array('formFactura' => $formFactura->createView(), 'active' => $active)
            );

        } elseif ($form['tipoForm'] == 'edit') {
            $factura = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:Factura'
            )->find($form['id']);
            $facturaType = new FacturaType();
            $facturaType->setAction($this->generateUrl('facturas_save'));
            $facturaType->setTipoForm('edit');

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

            $formFactura->handleRequest($this->getRequest());
            if ($formFactura->isValid()) {
                $itemSubtotalSinImp = 0;
                $itemSubtotalConImp = 0;
                $impuesto = 0;

                /* Procesamiento de productos asociados a la factura*/
                /**********************************************************************************************/
                $factItems = $factura->getFactItems();

                if (count($factItems) > 0) {
                    foreach ($factItems as $factItem) {
                        /*
                         * Si ya existe la linea de la cotización, actualizo los valores
                         * con los que traigo desde la vista.
                         */
                        if (!is_null($factItem->getId()) and $factItem->getId() > 0) {
                            $newFactItem = $factItem;
                            $factItem = $em->getRepository('FacturasBundle:FacturaItem')->find($factItem->getId());
                            /*
                             * Si está vacío el nuevo item, lo elimino
                             */
                            if (is_null($newFactItem->getProducto()) and (!is_null($newFactItem->getCantidad())
                                    or !is_null($newFactItem->getDescripcion())
                                    or !is_null($newFactItem->getPrecio()))
                            ) {
                                $formFactura->addError(new FormError("El campo del Producto no puede quedar vacío"));
                                $usuario = $this->get('security.context')->getToken()->getUser();
                                $pago = new Pago();

                                $pagoType = new PagoType();
                                $pagoType->setTipoForm('add');
                                $pagoType->setAction($this->generateUrl('pagos_save'));
                                $metodos = $this->getDoctrine()->getManager()->getRepository(
                                    'NomencladoresBundle:NomPagos'
                                )->findAll();
                                $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
                                $formPago = $this->createForm($pagoType, $pago);

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
                                        'formRec' => $formRec->createView(),
                                        'active' => $classActive
                                    )
                                );
                            }
                            if ((is_null($newFactItem->getCantidad())
                                and is_null($newFactItem->getDescripcion())
                                and is_null($newFactItem->getPrecio()))
                            ) {
                                $factura->removeFactItem($newFactItem);
                                if (!is_null($factItem)) {
                                    $this->getDoctrine()->getManager()->remove($factItem);
                                }
                                break;
                            }
                            /*
                             * Actualizo el item con los nuevos datos
                             * */
                            $factItem->setFactura($newFactItem->getFactura());
                            $factItem->setCantidad($newFactItem->getCantidad());
                            $factItem->setImpuesto($newFactItem->getImpuesto());
                            $factItem->setProducto($newFactItem->getProducto());
                            $factItem->setDescripcion($newFactItem->getDescripcion());
                            $factItem->setPrecio($newFactItem->getPrecio());
                            if (!is_null($factItem->getProducto())) {
                                foreach ($form['factItems'] as $item) {
                                    if (array_key_exists(
                                            'producto',
                                            $item
                                        ) and $item['producto'] == $factItem->getProducto()->getId()
                                    ) {
                                        $factItem->getProducto()->setDescripcion($item['descripcion']);
                                    }
                                }
                            }
                            $factItem->setTotal(($factItem->getCantidad() * $factItem->getPrecio()) +
                                (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad(
                                    ) * $factItem->getPrecio() * $factItem->getImpuesto()->getPorcentaje(
                                    ) / 100) : 0));
                            $itemSubtotalSinImp = $itemSubtotalSinImp + ($factItem->getCantidad(
                                    ) * $factItem->getPrecio());
                            $itemSubtotalConImp = $itemSubtotalConImp +
                                (($factItem->getCantidad() * $factItem->getPrecio()) +
                                    (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad(
                                        ) * $factItem->getPrecio() * $factItem->getImpuesto()->getPorcentaje(
                                        ) / 100) : 0));
                        } else {
                            if (is_null($factItem->getProducto())) {
                                $factura->removeFactItem($factItem);
                                if (!is_null($factItem)) {
                                    $this->getDoctrine()->getManager()->remove($factItem);
                                }
                                break;
                            } else {
                                foreach ($form['factItems'] as $item) {
                                    if (array_key_exists(
                                            'producto',
                                            $item
                                        ) and $item['producto'] == $factItem->getProducto()->getId()
                                    ) {
                                        $factItem->getProducto()->setDescripcion($item['descripcion']);
                                    }
                                }
                                $factItem->setTotal(($factItem->getCantidad() * $factItem->getPrecio()) +
                                    (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad(
                                        ) * $factItem->getPrecio() * $factItem->getImpuesto()->getPorcentaje(
                                        ) / 100) : 0));
                                $em->persist($factItem);
                                $itemSubtotalSinImp = $itemSubtotalSinImp + ($factItem->getCantidad(
                                        ) * $factItem->getPrecio());
                                $itemSubtotalConImp = $itemSubtotalConImp +
                                    (($factItem->getCantidad() * $factItem->getPrecio()) +
                                        (!is_null($factItem->getImpuesto()) ? ($factItem->getCantidad(
                                            ) * $factItem->getPrecio() * $factItem->getImpuesto()->getPorcentaje(
                                            ) / 100) : 0));
                            }
                        }
                    }
                }
                /**********************************************************************************************/
                /*Procesamiento de los impuestos asociados a la factura*/
                $factImps = $factura->getFactImps();
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
                            $factImp->setTotal($impuesto);
                            $em->persist($factImp);
                        } else {
                            $factura->removeFactImp($factImp);

                        }
                    }
                }
                /**********************************************************************************************/
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
                $factura->setUsuario($this->get('security.context')->getToken()->getUser());
                $em->persist($factura);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Factura actualizada exitosamente'
                );

                if ($factura->getEstado() == 'Enviado') {
                    return $this->sendMailAction($factura->getId());
                } else {
                    return $this->detailsAction($factura->getId());
                }
            }

            $usuario = $this->get('security.context')->getToken()->getUser();
            $pago = new Pago();

            $pagoType = new PagoType();
            $pagoType->setTipoForm('add');
            $pagoType->setAction($this->generateUrl('pagos_save'));
            $formPago = $this->createForm($pagoType, $pago);

            return $this->render(
                'FacturasBundle:Default:details.html.twig',
                array(
                    'factura' => $factura,
                    'formFactura' => $formFactura->createView(),
                    'usuario' => $usuario,
                    'formPago' => $formPago->createView(),
                    'active' => $classActive
                )
            );
        }

    }

    public function recurrenceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->getRequest()->get('formRec');
        if ($form['tipoForm'] == 'add') {
            $recurrencia[0] = new FacturaRec();
        } elseif ($form['tipoForm'] == 'edit') {
            $recurrencia = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaRec'
            )->findByFacBase($form['facBase']);
        }
        $factura = $this->getDoctrine()->getManager()->getRepository('FacturasBundle:Factura')->find(
            $form['facBase']
        );

        $recType = new FacturaRecType();
        $recType->setTipoForm('edit');
        $recType->setAction($this->generateUrl('facturas_recurrence'));

        $formRec = $this->createForm($recType, $recurrencia[0]);
        $formRec->handleRequest($this->getRequest());

        if ($formRec->isValid() && isset($form['intervalo'])) {
            switch ($form['intervalo']) {
                case 'Día(s)':
                    $recurrencia[0]->setDia(true);
                    $recurrencia[0]->setSemana(false);
                    $recurrencia[0]->setMes(false);
                    $recurrencia[0]->setAnno(false);
                    break;
                case 'Semana(s)':
                    $recurrencia[0]->setDia(false);
                    $recurrencia[0]->setSemana(true);
                    $recurrencia[0]->setMes(false);
                    $recurrencia[0]->setAnno(false);
                    break;
                case 'Mes(es)':
                    $recurrencia[0]->setMes(true);
                    $recurrencia[0]->setSemana(false);
                    $recurrencia[0]->setDia(false);
                    $recurrencia[0]->setAnno(false);
                    break;
                case 'Año(s)':
                    $recurrencia[0]->setAnno(true);
                    $recurrencia[0]->setSemana(false);
                    $recurrencia[0]->setMes(false);
                    $recurrencia[0]->setDia(false);
                    break;
            }
            $recurrencia[0]->setActivo(true);
            $recurrencia[0]->setProxFecha(new \DateTime('now'));
            $recurrencia[0]->setFacBase($factura->getId());
            $em->persist($recurrencia[0]);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_add',
                'Recurrencia establecida exitosamente'
            );

            return $this->redirect($this->generateUrl('facturas_detail', array('id' => $recurrencia[0]->getFacBase())));
        }

        $this->get('session')->getFlashBag()->add(
            'info_error',
            'Ha ocurrido un error tratando de establecer la recurrencia'
        );

        return $this->detailsAction($factura->getId());

    }

    public function recDeleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $recurrencia = $em->getRepository('FacturasBundle:FacturaRec')->find($id);
        $facId = $recurrencia->getFacBase();
        $em->remove($recurrencia);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_delete',
            'Recurrencia eliminada correctamente'
        );

        return $this->detailsAction($facId);
    }

    public function recActivateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $recurrencia = $em->getRepository('FacturasBundle:FacturaRec')->find($id);
        $facId = $recurrencia->getFacBase();
        $recurrencia->setActivo(!$recurrencia->getActivo());
        $em->persist($recurrencia);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'info_edit',
            'Recurrencia modificada correctamente'
        );

        return $this->detailsAction($facId);
    }

    public function previewAction($id, $preview)
    {
        $classActive = array('sup' => 'Facturas', 'sub' => '');
        $em = $this->getDoctrine()->getManager();
        $factura = $em->getRepository('FacturasBundle:Factura')->find($id);
        $importeFacturaSinImp = 0;
        $importeFacturaConImp = 0;

        /*Cargo los productos de la factura*/
        /*********************************************************************************/
        if (is_null($factura->getFactItems())) {
            $factItems = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaItem'
            )->findByFactura($factura->getId());
            if (count($factItems) > 0) {
                foreach ($factItems as $factItem) {
                    $factura->addFactItem($factItem);
                }
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
        /*********************************************************************************/
        /*Cargo los impuestos de la factura*/
        /*********************************************************************************/
        if (is_null($factura->getFactImps())) {
            $factImps = $this->getDoctrine()->getManager()->getRepository(
                'FacturasBundle:FacturaImp'
            )->findByFactura($factura->getId());
            if (count($factImps) > 0) {
                foreach ($factImps as $factImp) {
                    $factura->addFactImp($factImp);
                }
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
        /*********************************************************************************/

        $usuario = $factura->getUsuario();

        if ($preview == '1') {
            return $this->render(
                'FacturasBundle:Default:preview.html.twig',
                array('factura' => $factura, 'usuario' => $usuario, 'preview' => $preview, 'active' => $classActive)
            );
        } elseif ($preview == '0') {
            return $this->render(
                'FacturasBundle:Default:pdfFile.html.twig',
                array('factura' => $factura, 'usuario' => $usuario, 'preview' => $preview, 'active' => $classActive)
            );
        }

    }

    public function pdfAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $pdfObj = $this->get("white_october.tcpdf")->create();
        $pdfObj->SetAuthor('Kepnix Capital E.I.R.L');
        $pdfObj->SetTitle('Factura ' . $em->getRepository('FacturasBundle:Factura')->find($id)->getCodigo());
        $pdfObj->SetSubject('Factura');
        $pdfObj->SetKeywords('Factura');
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
                'Content-Disposition' => 'attachment; filename="factura.pdf"'
            )
        );
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $factura = $em->find('FacturasBundle:Factura', $id);
        if (!$factura) {
            $this->createNotFoundException('No se encontró la factura.');
        } else {
            $factItems = $em->getRepository('FacturasBundle:FacturaItem')->findByFactura($id);
            $factImps = $em->getRepository('FacturasBundle:FacturaImp')->findByFactura($id);
            $pagos = $em->getRepository('PagosBundle:Pago')->findByFactura($id);
            foreach ($factItems as $factItem) {
                $em->remove($factItem);
            }
            foreach ($factImps as $factImp) {
                $em->remove($factImp);
            }
            foreach ($pagos as $pago) {
                $em->remove($pago);
            }
            $em->remove($factura);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Factura eliminada exitosamente'
            );

            return $this->redirect($this->generateUrl('facturas_homepage'));
        }
    }

    public function sendMailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $factura = $em->getRepository('FacturasBundle:Factura')->find($id);
        $cuerpo = $this->previewAction($id, '0');
        $mensaje = \Swift_Message::newInstance()
            ->setSubject('Factura ' . $factura->getCodigo())
            ->setCc('ventas@conexionporsatelite.com')
            ->setFrom($factura->getUsuario()->getEmail())
            ->setTo($factura->getCliente()->getEmail())
            ->setBody($cuerpo, 'text/html');
        try {
            $this->container->get('mailer')->send($mensaje);

            $this->get('session')->getFlashBag()->add(
                'info_edit',
                'Email enviado exitosamente a ' . $factura->getCliente()->getEmail()
            );
        } catch (\Swift_TransportException $e) {
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                $e->getMessage()
            );
        }

        return $this->detailsAction($id);
    }

    public function getInsertForm($factura)
    {
        $em = $this->getDoctrine()->getManager();
        $clientes = $em->getRepository('ClientesBundle:Cliente')->findBy(
            array('activo' => 1)
        );
        $clientes = new ChoiceList($clientes, $clientes);
        $tdocConf = $em->getRepository('NomencladoresBundle:NomTdocConf')->findByTdocCod('FAC');
        $tdocConf = new ChoiceList($tdocConf, $tdocConf);
        $form = $this->createFormBuilder($factura)
            ->setAttribute('name', 'formFactura')
            ->setAttribute('id', 'formFactura')
            ->setAction($this->generateUrl('facturas_save'))
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

    /*AJAX SOURCE*/

    public function listAction(Request $request)
    {
        $get = $request->request->all();
        $session = $request->getSession();
        $filters = $session->get('filters') != null ? $filters = $session->get('filters') : array();

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('id', 'estado', 'codigo', 'fechaVenc', 'cliente', 'importe', 'saldo');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('FacturasBundle:Factura')->ajaxTable($get, $filters, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('FacturasBundle:Factura')->getCount());
        $query = $em->getRepository('FacturasBundle:Factura')->ajaxTable($get, $filters, true);
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
                            case "Vencido":
                                $row[] = '<span class="label label-overdue">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
                                break;
                            case "Pagado":
                                $row[] = '<span class="label label-paid">' . $aRow[$columns[$i]]['descripcion'] . '</span>';
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
                    case "saldo":
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

