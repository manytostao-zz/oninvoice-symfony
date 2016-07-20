<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 08/07/2015
 * Time: 11:23
 */

namespace FusionClone\NomencladoresBundle\Controller;


use Doctrine\DBAL\DBALException;
use FusionClone\NomencladoresBundle\Entity\NomImp;
use FusionClone\NomencladoresBundle\Form\NomImpType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ImpuestoController
 * @package FusionClone\NomencladoresBundle\Controller
 */
class ImpuestoController extends Controller
{
    /**
     * @return Response
     */
    public function listImpAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $session->remove('filters');

        return $this->render('NomencladoresBundle:Impuestos:listImp.html.twig');
    }

    /**
     * @return Response
     */
    public function createImpAction()
    {
        $impuesto = new NomImp();

        $impuestoType = new NomImpType();
        $impuestoType->setTipoForm('add');
        $impuestoType->setAction($this->generateUrl('nomencladores_saveImp'));
        $formImp = $this->createForm($impuestoType, $impuesto);

        return $this->render(
            'NomencladoresBundle:Impuestos:createImp.html.twig',
            array('formImp' => $formImp->createView())
        );
    }

    /**
     * @param $id
     * @return Response
     */
    public function editImpAction($id)
    {
        $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find($id);

        $impuestoType = new NomImpType();
        $impuestoType->setId($id);
        $impuestoType->setTipoForm('edit');
        $impuestoType->setAction($this->generateUrl('nomencladores_saveImp'));
        $formImp = $this->createForm($impuestoType, $impuesto);

        return $this->render(
            'NomencladoresBundle:Impuestos:createImp.html.twig',
            array('formImp' => $formImp->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function saveImpAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formImp');
        if ($form['tipoForm'] == 'add') {
            $impuesto = new NomImp();
        } elseif ($form['tipoForm'] == 'edit') {
            $impuesto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomImp')->find(
                $form['id']
            );
        }

        $impuestoType = new NomImpType();
        $impuestoType->setAction($this->generateUrl('nomencladores_saveImp'));
        $impuestoType->setTipoForm($form['tipoForm']);

        $formImp = $this->createForm($impuestoType, $impuesto);

        $formImp->handleRequest($peticion);
        if ($formImp->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($impuesto);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Impuesto creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Impuesto actualizado exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listImp'));

        }

        return $this->render(
            'NomencladoresBundle:Impuestos:createImp.html.twig',
            array('formImp' => $formImp->createView())
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteImpAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $impuesto = $em->find('NomencladoresBundle:NomImp', $id);

        if (!$impuesto) {
            return $this->createNotFoundException('No se encontró el Impuesto.');
        } else {
            try {
                $em->remove($impuesto);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Impuesto eliminado exitosamente'
                );
            } catch (DBALException $e) {

                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No se podido eliminar el impuesto pues está siendo utilizado'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listImp'));
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
        $columns = array('id', 'nombre', 'porcentaje');
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
        $rResult = $em->getRepository('NomencladoresBundle:NomImp')->ajaxTable(
            $get,
            $filters,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladoresBundle:NomImp')->getCount());
        $query = $em->getRepository('NomencladoresBundle:NomImp')->ajaxTable($get, $filters, true);
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
                    case 'porcentaje':
                        $row[] = '%' . number_format(
                                $aRow[$columns[$i]],
                                $cantidadDecimales->getValor(),
                                $monedaBase->getSignDecimal(),
                                $monedaBase->getSignMillares()
                            );
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