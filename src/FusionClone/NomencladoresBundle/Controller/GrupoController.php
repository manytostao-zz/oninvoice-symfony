<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 08/07/2015
 * Time: 11:23
 */

namespace FusionClone\NomencladoresBundle\Controller;


use Doctrine\DBAL\DBALException;
use FusionClone\NomencladoresBundle\Entity\NomTdocConf;
use FusionClone\NomencladoresBundle\Form\NomTdocConfType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GrupoController
 * @package FusionClone\NomencladoresBundle\Controller
 */
class GrupoController extends Controller
{
    /**
     * @return Response
     */
    public function listGrupoAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $session->remove('filters');

        return $this->render('NomencladoresBundle:Grupo:listGrupo.html.twig');
    }

    /**
     * @return Response
     */
    public function createGrupoAction()
    {
        $grupo = new NomTdocConf();

        $grupoType = new NomTdocConfType();
        $grupoType->setTipoForm('add');
        $grupoType->setAction($this->generateUrl('nomencladores_saveGrupo'));
        $tdocs = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomTdoc')->findAll();
        $grupoType->setTdocChoices(new ChoiceList($tdocs, $tdocs));
        $formGrupo = $this->createForm($grupoType, $grupo);

        return $this->render(
            'NomencladoresBundle:Grupo:createGrupo.html.twig',
            array('formGrupo' => $formGrupo->createView())
        );
    }

    /**
     * @param $id
     * @return Response
     */
    public function editGrupoAction($id)
    {
        $grupo = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomTdocConf')->find($id);

        $grupoType = new NomTdocConfType();
        $grupoType->setId($id);
        $grupoType->setTipoForm('edit');
        $tdocs = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomTdoc')->findAll();
        $grupoType->setTdocChoices(new ChoiceList($tdocs, $tdocs));
        $grupoType->setAction($this->generateUrl('nomencladores_saveGrupo'));
        $formGrupo = $this->createForm($grupoType, $grupo);

        return $this->render(
            'NomencladoresBundle:Grupo:createGrupo.html.twig',
            array('formGrupo' => $formGrupo->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function saveGrupoAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formGrupo');
        if ($form['tipoForm'] == 'add') {
            $grupo = new NomTdocConf();
        } elseif ($form['tipoForm'] == 'edit') {
            $grupo = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomTdocConf')->find(
                $form['id']
            );
        }

        $grupoType = new NomTdocConfType();
        $grupoType->setAction($this->generateUrl('nomencladores_saveGrupo'));
        $grupoType->setTipoForm($form['tipoForm']);
        $tdocs = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomTdoc')->findAll();
        $grupoType->setTdocChoices(new ChoiceList($tdocs, $tdocs));

        $formGrupo = $this->createForm($grupoType, $grupo);

        $formGrupo->handleRequest($peticion);
        if ($formGrupo->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($grupo);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Grupo creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Grupo actualizado exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listGrupo'));

        }

        return $this->render(
            'NomencladoresBundle:Grupo:createGrupo.html.twig',
            array('formGrupo' => $formGrupo->createView())
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteGrupoAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $grupo = $em->find('NomencladoresBundle:NomTdocConf', $id);

        if (!$grupo) {
            $this->createNotFoundException('No se encontró el Grupo.');
        } else {
            try {
                $em->remove($grupo);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Grupo eliminado exitosamente'
                );
            }catch(DBALException $e){

                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No se ha podido eliminar el grupo pues está siendo utilizado'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listGrupo'));
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
        $columns = array('id', 'descripcion', 'prefijo', 'consecutivo', 'cantDigCons', 'anno', 'mes');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('NomencladoresBundle:NomTdocConf')->ajaxTable(
            $get,
            $filters,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladoresBundle:NomTdocConf')->getCount());
        $query = $em->getRepository('NomencladoresBundle:NomTdocConf')->ajaxTable($get, $filters, true);
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
                    case "mes":
                    case "anno":
                        if ($aRow[$columns[$i]]) {
                            $row[] = "Si";
                        } else {
                            $row[] = "No";
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