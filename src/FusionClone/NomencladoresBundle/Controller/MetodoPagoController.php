<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 08/07/2015
 * Time: 11:23
 */

namespace FusionClone\NomencladoresBundle\Controller;


use Doctrine\DBAL\DBALException;
use FusionClone\NomencladoresBundle\Entity\NomPagos;
use FusionClone\NomencladoresBundle\Form\NomPagosType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MetodoPagoController
 * @package FusionClone\NomencladoresBundle\Controller
 */
class MetodoPagoController extends Controller
{
    /**
     * @return Response
     */
    public function listPagosAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $session->remove('filters');

        return $this->render('NomencladoresBundle:Pagos:listPagos.html.twig');
    }

    /**
     * @return Response
     */
    public function createPagosAction()
    {
        $pago = new NomPagos();

        $pagoType = new NomPagosType();
        $pagoType->setTipoForm('add');
        $pagoType->setAction($this->generateUrl('nomencladores_savePagos'));
        $formPago = $this->createForm($pagoType, $pago);

        return $this->render(
            'NomencladoresBundle:Pagos:createPago.html.twig',
            array('formPago' => $formPago->createView())
        );
    }

    /**
     * @param $id
     * @return Response
     */
    public function editPagosAction($id)
    {
        $pago = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->find($id);

        $pagoType = new NomPagosType();
        $pagoType->setId($id);
        $pagoType->setTipoForm('edit');
        $pagoType->setAction($this->generateUrl('nomencladores_savePagos'));
        $formPago = $this->createForm($pagoType, $pago);

        return $this->render(
            'NomencladoresBundle:Pagos:createPago.html.twig',
            array('formPago' => $formPago->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function savePagosAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formPago');
        if ($form['tipoForm'] == 'add') {
            $pago = new NomPagos();
        } elseif ($form['tipoForm'] == 'edit') {
            $pago = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomPagos')->find(
                $form['id']
            );
        }

        $pagoType = new NomPagosType();
        $pagoType->setAction($this->generateUrl('nomencladores_savePagos'));
        $pagoType->setTipoForm($form['tipoForm']);

        $formPago = $this->createForm($pagoType, $pago);

        $formPago->handleRequest($peticion);
        if ($formPago->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($pago);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Método de Pago creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Método de Pago actualizado exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listPagos'));

        }

        return $this->render(
            'NomencladoresBundle:Pagos:createPago.html.twig',
            array('formPago' => $formPago->createView())
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePagosAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $pago = $em->find('NomencladoresBundle:NomPagos', $id);

        if (!$pago) {
            $this->createNotFoundException('No se encontró el Método de Pago.');
        } else {
            try{
            $em->remove($pago);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'info_delete',
                'Método de Pago eliminado exitosamente'
            );}catch (DBALException $e){
                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No se ha podido eliminar el método de pago pues está siendo utilizado'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listPagos'));
        }
    }

    /*AJAXSOURCE*/
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $get = $request->request->all();
        $session = $request->getSession();
        $filters = $session->get('filters') != null ? $filters = $session->get('filters') : array();

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
        * you want to insert a non-database field (for example a counter or static image)
        */
        $columns = array('id', 'nombre');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('NomencladoresBundle:NomPagos')->ajaxTable(
            $get,
            $filters,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladoresBundle:NomPagos')->getCount());
        $query = $em->getRepository('NomencladoresBundle:NomPagos')->ajaxTable($get, $filters, true);
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