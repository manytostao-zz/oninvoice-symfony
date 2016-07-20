<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 08/07/2015
 * Time: 11:23
 */

namespace FusionClone\NomencladoresBundle\Controller;


use Doctrine\DBAL\DBALException;
use FusionClone\NomencladoresBundle\Entity\NomMone;
use FusionClone\NomencladoresBundle\Form\NomMoneType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MonedaController
 * @package FusionClone\NomencladoresBundle\Controller
 */
class MonedaController extends Controller
{
    /**
     * @return Response
     */
    public function listMoneAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $session->remove('filters');

        return $this->render('NomencladoresBundle:Moneda:listMone.html.twig');
    }

    /**
     * @return Response
     */
    public function createMoneAction()
    {
        $moneda = new NomMone();

        $monedaType = new NomMoneType();
        $monedaType->setTipoForm('add');
        $monedaType->setAction($this->generateUrl('nomencladores_saveMone'));
        $formMoneda = $this->createForm($monedaType, $moneda);

        return $this->render(
            'NomencladoresBundle:Moneda:createMone.html.twig',
            array('formMoneda' => $formMoneda->createView())
        );
    }

    /**
     * @param $id
     * @return Response
     */
    public function editMoneAction($id)
    {
        $moneda = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomMone')->find($id);

        $monedaType = new NomMoneType();
        $monedaType->setId($id);
        $monedaType->setTipoForm('edit');
        $monedaType->setAction($this->generateUrl('nomencladores_saveMone'));
        $formMoneda = $this->createForm($monedaType, $moneda);

        return $this->render(
            'NomencladoresBundle:Moneda:createMone.html.twig',
            array('formMoneda' => $formMoneda->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function saveMoneAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formMoneda');
        if ($form['tipoForm'] == 'add') {
            $moneda = new NomMone();
        } elseif ($form['tipoForm'] == 'edit') {
            $moneda = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomMone')->find(
                $form['id']
            );
        }

        $monedaType = new NomMoneType();
        $monedaType->setAction($this->generateUrl('nomencladores_saveMone'));
        $monedaType->setTipoForm($form['tipoForm']);

        $formMoneda = $this->createForm($monedaType, $moneda);

        $formMoneda->handleRequest($peticion);
        if ($formMoneda->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($moneda);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Moneda creada exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Moneda actualizada exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listMone'));

        }

        return $this->render(
            'NomencladoresBundle:Moneda:createMone.html.twig',
            array('formMoneda' => $formMoneda->createView())
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMoneAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $moneda = $em->find('NomencladoresBundle:NomMone', $id);

        if (!$moneda) {
            $this->createNotFoundException('No se encontró la moneda.');
        } else {
            try {
                $em->remove($moneda);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Moneda eliminada exitosamente'
                );
            }catch(DBALException $e){
                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No es posible eliminar la moneda pues está siendo usada en documentos'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listMone'));
        }
    }

    /*AJAX SOURCE*/
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
        $columns = array('id', 'descripcion', 'codigo', 'simbolo', 'ubicaSimbol');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('NomencladoresBundle:NomMone')->ajaxTable($get, $filters, true)->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladoresBundle:NomMone')->getCount());
        $query = $em->getRepository('NomencladoresBundle:NomMone')->ajaxTable($get, $filters, true);
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
                    case "ubicaSimbol":
                        if ($aRow[$columns[$i]]) {
                            $row[] = "Antes de importe";
                        } else {
                            $row[] = "Después de importe";
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