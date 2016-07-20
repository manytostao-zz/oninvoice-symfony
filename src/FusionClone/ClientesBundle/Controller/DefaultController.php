<?php

namespace FusionClone\ClientesBundle\Controller;

use Doctrine\DBAL\DBALException;
use FusionClone\ClientesBundle\Entity\Cliente;
use FusionClone\ClientesBundle\Form\ClienteType;
use FusionClone\PagosBundle\Form\PagoType;
use FusionClone\PagosBundle\Entity\Pago;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $classActive = array('sup' => 'Clientes', 'sub' => 'Ver');
        if (is_null($this->getRequest()->get('status'))) {
            $classActive['pill'] = 'Todos';
            $session->remove('filters');
        } else {
            $session->set('filters', array('activo' => $this->getRequest()->get('status')));
            if ($this->getRequest()->get('status') == '1') {
                $classActive['pill'] = 'Activo';

            } else {
                $classActive['pill'] = 'Inactivo';
            }
        }

        $peticion->setSession($session);

        return $this->render(
            'ClientesBundle:Default:index.html.twig',
            array(
                'active' => $classActive,
                'status' => $peticion->get('status')
            )
        );
    }

    public function detailsAction($id)
    {
        $cliente = $this->getDoctrine()->getManager()->getRepository(
            'ClientesBundle:Cliente'
        )->find($id);

        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();

        $sesion->set('filters', array('cliente' => $cliente->getId()));
        $peticion->setSession($sesion);

        $classActive = array('sup' => 'Clientes', 'sub' => '');
        if (is_null($this->getRequest()->get('status'))) {
            $classActive['pill'] = 'Todos';
            //$clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findAll();
        } else {
            /* $clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findBy(
                 array('activo' => $this->getRequest()->get('status'))
             );*/

            if ($this->getRequest()->get('status') == '1') {
                $classActive['pill'] = 'Activo';
            } else {
                $classActive['pill'] = 'Inactivo';
            }
        }

        $em = $this->getDoctrine()->getManager();
        $facturas = $em->getRepository('FacturasBundle:Factura')->findByCliente($id);


        //$facturas = $this->getDoctrine()->getManager()->getRepository('FacturasBundle:Factura')->findByCliente($id);
        $facturado = 0.000;
        foreach ($facturas as $factura) {
            $facturado = $facturado + $factura->getImporte();
        }
        $pagos = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')->findByCliente($id);
        $pagado = 0.000;
        foreach ($pagos as $pago) {
            $pagado = $pagado + $pago->getImporte();
        }
        $saldo = $facturado - $pagado;

        $cliente->setSaldo($saldo);
        $cliente->setFacturado($facturado);
        $cliente->setPagado($pagado);

        $pago = new Pago();

        $pagoType = new PagoType();
        $pagoType->setAction($this->generateUrl('pagos_save'));
        $pagoType->setTipoForm('add');
        $metodos = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->findAll();
        $pagoType->setMetodos(new ChoiceList($metodos, $metodos));
        $formPago = $this->createForm($pagoType, $pago);

        return $this->render(
            'ClientesBundle:Default:details.html.twig',
            array(
                'active' => $classActive,
                'cliente' => $cliente,
                'pagos' => $pagos,
                'status' => $peticion->get('status'),
                'itemsPerPage' => $sesion->get('itemsPerPage'),
                'formPago' => $formPago->createView()
            )
        );
    }

    public function createAction()
    {
        $active = array('sup' => 'Clientes', 'sub' => 'Crear');
        $cliente = new Cliente();

        $choices = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomMone'
        )->findAll();
        $moneChoices = new ChoiceList($choices, $choices);
        $clienteType = new ClienteType();
        $clienteType->setMoneChoices($moneChoices);
        $clienteType->setAction($this->generateUrl('clientes_save'));
        $clienteType->setTipoForm('add');
        $options = array('label' => 'formCliente');

        $formCliente = $this->createForm($clienteType, $cliente, $options);

        return $this->render(
            'ClientesBundle:Default:create.html.twig',
            array('formCliente' => $formCliente->createView(), 'active' => $active)
        );
    }

    public function editAction($id)
    {
        $active = array('sup' => 'Clientes', 'sub' => 'Crear');
        $cliente = $this->getDoctrine()->getManager()->getRepository(
            'ClientesBundle:Cliente'
        )->find($id);

        $choices = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomMone'
        )->findAll();
        $moneChoices = new ChoiceList($choices, $choices);
        $clienteType = new ClienteType();
        $clienteType->setMoneChoices($moneChoices);
        $clienteType->setAction($this->generateUrl('clientes_save'));
        $clienteType->setTipoForm('edit');
        $clienteType->setId($id);
        $options = array('label' => 'formCliente');

        $formCliente = $this->createForm($clienteType, $cliente, $options);

        return $this->render(
            'ClientesBundle:Default:create.html.twig',
            array('formCliente' => $formCliente->createView(), 'active' => $active)
        );
    }

    public function saveAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formCliente');
        if ($form['tipoForm'] == 'add') {
            $cliente = new Cliente();
        } elseif ($form['tipoForm'] == 'edit') {
            $cliente = $this->getDoctrine()->getManager()->getRepository(
                'ClientesBundle:Cliente'
            )->find($form['id']);
        }

        $choices = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomMone'
        )->findAll();
        $moneChoices = new ChoiceList($choices, $choices);
        $clienteType = new ClienteType();
        $clienteType->setMoneChoices($moneChoices);
        $clienteType->setAction($this->generateUrl('clientes_save'));
        $clienteType->setTipoForm($form['tipoForm']);
        $options = array('label' => 'formCliente');

        $formCliente = $this->createForm($clienteType, $cliente, $options);

        $formCliente->handleRequest($peticion);
        if ($formCliente->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($cliente);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Registro creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Registro actualizado exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('clientes_homepage'));

        }

        $active = array('sup' => 'Clientes', 'sub' => 'Crear');

        return $this->render(
            'ClientesBundle:Default:create.html.twig',
            array('formCliente' => $formCliente->createView(), 'active' => $active)
        );
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $cliente = $em->find('ClientesBundle:Cliente', $id);

        if (!$cliente) {
            $this->createNotFoundException('No se encontró el cliente.');
        } else {
            try {
                $em->remove($cliente);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Registro eliminado exitosamente'
                );

            }catch (DBALException $e) {
                $this->getDoctrine()->resetManager();
                $em = $this->getDoctrine()->getManager();
                $cliente = $em->find('ClientesBundle:Cliente', $id);
                $cliente->setActivo(false);
                $em->persist($cliente);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Registro desactivado exitosamente'
                );
            }
            return $this->redirect($this->generateUrl('clientes_homepage'));
        }
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
        $columns = array('id', 'nombre', 'email', 'telefono', 'saldo', 'activo');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('ClientesBundle:Cliente')->ajaxTable($get, $filters, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('ClientesBundle:Cliente')->getCount());
        $query = $em->getRepository('ClientesBundle:Cliente')->ajaxTable($get, $filters, true);
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
                    case "activo":
                        $row[] = ($aRow[$columns[$i]] == 1) ? 'Si' : 'No';
                        break;
                    case "saldo":
                        $monedaParam = $em->getRepository('OtrosBundle:AppParam')->findByNombre('MONEDA_BASE');
                        $moneda = $em->getRepository('NomencladoresBundle:NomMone')->findByCodigo(
                            $monedaParam[0]->getValor()
                        );
                        $facturas = $this->getDoctrine()->getManager()->getRepository('FacturasBundle:Factura')
                            ->findByCliente($aRow["id"]);
                        $facturado = 0.000;
                        foreach ($facturas as $factura) {
                            $facturado = $facturado + $factura->getImporte();
                        }
                        $pagos = $this->getDoctrine()->getManager()->getRepository('PagosBundle:Pago')
                            ->findByCliente($aRow["id"]);
                        $pagado = 0.000;
                        foreach ($pagos as $pago) {
                            $pagado = $pagado + $pago->getImporte();
                        }
                        $saldo = $facturado - $pagado;

                        $saldo = number_format($saldo,
                            3,
                            $moneda[0]->getSignDecimal(),
                            $moneda[0]->getSignMillares()
                        );
                        if ($moneda[0]->getUbicaSimbol()) {
                            $row[] = $moneda[0]->getSimbolo() . $saldo;
                        } else {
                            $row[] = $saldo . $moneda[0]->getSimbolo();
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
