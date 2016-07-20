<?php

namespace FusionClone\UsuariosBundle\Controller;

use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use FusionClone\UsuariosBundle\Entity\Usuario;
use FusionClone\UsuariosBundle\Form\UsuarioType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

/**
 * Class DefaultController
 * @package FusionClone\UsuariosBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();
        $error = $peticion->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $sesion->get(SecurityContext::AUTHENTICATION_ERROR)
        );

        /*$new = new Usuario();
        $new->setNombre('Kepnix Capital E.I.R.L');
        $new->setPassword('demoPass');
        $new->setEmail('demo@kepnix.com');
        $new->setCompannia('PENIEL Virtual');
        $new->setDireccion('31A #2609 e/ 26 y 30, Playa. La Habana.');
        $new->setFax(21546328);
        $new->setTelefono(2060298);
        $new->setMovil(0521254136);
        $new->setWebpage('www.kepnix.com');
        $encoder = $this->get('security.encoder_factory')
            ->getEncoder($new);
        $new->setSalt(md5(time()));
        $passwordCodificado = $encoder->encodePassword(
            $new->getPassword(),
            $new->getSalt()
        );
        $new->setPassword($passwordCodificado);

        $em = $this->getDoctrine()->getManager();
        $em->persist($new);
        $em->flush();*/

        return $this->render(
            'UsuariosBundle:Default:logincc.html.twig',
            array(
                'last_username' => $sesion->get(SecurityContext::LAST_USERNAME),
                'error' => $error
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();

        $sesion->remove('filters');

        return $this->render('UsuariosBundle:Default:list.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $usuario = new Usuario();

        $usuarioType = new UsuarioType();
        if ($this->getRequest()->get('addAsocClie')) {
            $pill = 'Clie';
            $tipoForm = 'addAsocClie';
        } else {
            $pill = 'Admin';
            $tipoForm = 'add';
        }
        $usuarioType->setTipoForm($tipoForm);
        $usuarioType->setAction($this->generateUrl('usuarios_save'));
        $clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findAll();
        $usuarioType->setClientesChoices(new ChoiceList($clientes, $clientes));
        $formUsua = $this->createForm($usuarioType, $usuario);

        return $this->render(
            'UsuariosBundle:Default:create.html.twig',
            array('formUsua' => $formUsua->createView(), 'tipoForm' => $tipoForm, 'pill' => $pill)
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $usuario = $this->getDoctrine()->getManager()->getRepository('UsuariosBundle:Usuario')->find($id);

        $usuarioType = new UsuarioType();
        $usuarioType->setId($id);
        $clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findAll();
        $usuarioType->setClientesChoices(new ChoiceList($clientes, $clientes));
        if (!is_null($usuario->getCliente())) {
            $tipoForm = 'editAsocClie';
            $usuarioType->setDataClie($this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->find($usuario->getCliente()));
        } else {
            $tipoForm = 'edit';
        }
        $usuarioType->setTipoForm($tipoForm);
        $usuarioType->setAction($this->generateUrl('usuarios_save'));

        $formUsua = $this->createForm($usuarioType, $usuario);


        return $this->render(
            'UsuariosBundle:Default:create.html.twig',
            array('formUsua' => $formUsua->createView(), 'tipoForm' => $tipoForm)
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editPassAction($id)
    {
        $usuario = $this->getDoctrine()->getManager()->getRepository('UsuariosBundle:Usuario')->find($id);

        $usuarioType = new UsuarioType();
        $usuarioType->setId($id);
        $usuarioType->setTipoForm('reset');
        $usuarioType->setAction($this->generateUrl('usuarios_save'));
        $clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findAll();
        $usuarioType->setClientesChoices(new ChoiceList($clientes, $clientes));
        $formUsua = $this->createForm($usuarioType, $usuario);


        return $this->render(
            'UsuariosBundle:Default:create.html.twig',
            array('formUsua' => $formUsua->createView(), 'tipoForm' => 'reset')
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function saveAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formUsua');
        if ($form['tipoForm'] == 'add' or $form['tipoForm'] == 'addAsocClie') {
            $usuario = new Usuario();
        } elseif ($form['tipoForm'] == 'edit' or $form['tipoForm'] == 'reset' or $form['tipoForm'] == 'editAsocClie') {
            $usuario = $this->getDoctrine()->getManager()->getRepository('UsuariosBundle:Usuario')->find(
                $form['id']
            );
        }


        $id = $form['id'];
        $usuarioType = new UsuarioType();
        $usuarioType->setAction($this->generateUrl('usuarios_save'));
        $clientes = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->findAll();
        $usuarioType->setClientesChoices(new ChoiceList($clientes, $clientes));
        $usuarioType->setTipoForm($form['tipoForm']);

        /*Copio los datos previos*/
        $prevUsua = new Usuario();
        $prevUsua->setNombre($usuario->getNombre());
        $prevUsua->setPassword($usuario->getPassword());
        $prevUsua->setSalt($usuario->getSalt());
        $prevUsua->setDireccion($usuario->getDireccion());
        $prevUsua->setCompannia($usuario->getCompannia());
        $prevUsua->setEmail($usuario->getEmail());
        $prevUsua->setFax($usuario->getFax());
        $prevUsua->setFechaAlta($usuario->getFechaAlta());
        $prevUsua->setMovil($usuario->getMovil());
        $prevUsua->setTelefono($usuario->getTelefono());
        $prevUsua->setWebpage($usuario->getWebpage());
        $prevUsua->setCliente($usuario->getCliente());

        $formUsua = $this->createForm($usuarioType, $usuario);

        $formUsua->handleRequest($peticion);
        if ($formUsua->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($form['tipoForm'] == 'add' or $form['tipoForm'] == 'reset') {
                if ($form['tipoForm'] == 'reset') {

                    $usuario->setNombre($prevUsua->getNombre());
                    $usuario->setDireccion($prevUsua->getDireccion());
                    $usuario->setCompannia($prevUsua->getCompannia());
                    $usuario->setEmail($prevUsua->getEmail());
                    $usuario->setFax($prevUsua->getFax());
                    $usuario->setFechaAlta($prevUsua->getFechaAlta());
                    $usuario->setMovil($prevUsua->getMovil());
                    $usuario->setTelefono($prevUsua->getTelefono());
                    $usuario->setWebpage($prevUsua->getWebpage());
                    $usuario->setCliente($prevUsua->getCliente());
                }
                $encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
                $usuario->setSalt(md5(time()));
                $passwordCodificado = $encoder->encodePassword(
                    $usuario->getPassword(),
                    $usuario->getSalt()
                );
                $usuario->setPassword($passwordCodificado);

            } elseif ($form['tipoForm'] == 'edit') {
                $usuario->setPassword($prevUsua->getPassword());
            } elseif ($form['tipoForm'] == 'addAsocClie' or $form['tipoForm'] == 'editAsocClie') {
                $cliente = $this->getDoctrine()->getManager()->getRepository('ClientesBundle:Cliente')->find(
                    $usuario->getCliente()
                );
                $usuario->setNombre($cliente->getNombre());
                $usuario->setDireccion($cliente->getDireccion());
                //$usuario->setCompannia($cliente->getCompannia());
                $usuario->setEmail($cliente->getEmail());
                $usuario->setFax($cliente->getFax());
                $usuario->setFechaAlta($prevUsua->getFechaAlta());
                $usuario->setMovil($cliente->getMovil());
                $usuario->setTelefono($cliente->getTelefono());
                $usuario->setWebpage($cliente->getWebpage());
                $usuario->setCliente($cliente->getId());
                $encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
                $usuario->setSalt(md5(time()));
                $passwordCodificado = $encoder->encodePassword(
                    $usuario->getPassword(),
                    $usuario->getSalt()
                );
                $usuario->setPassword($passwordCodificado);
            }
            $em->persist($usuario);
            $em->flush();

            if ($form['tipoForm'] == 'add' or $form['tipoForm'] == 'addAsocClie') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Usuario creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Usuario actualizado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'reset') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Contraseña actualizada exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('usuarios_list'));

        }

        return $this->render(
            'UsuariosBundle:Default:create.html.twig',
            array('formUsua' => $formUsua->createView(), 'tipoForm' => $form['tipoForm'])
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->find('UsuariosBundle:Usuario', $id);

        if (!$usuario) {
            throw $this->createNotFoundException('No se encontró el usuario');
        } else {
            try {
                $em->remove($usuario);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Usuario eliminado exitosamente'
                );
            }catch(DBALException $e){
                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No se ha podido eliminar el usuario porque está siendo utilizado'
                );
            }

            return $this->redirect($this->generateUrl('usuarios_list'));
        }
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
        $columns = array('id', 'nombre', 'email');
        $get['columns'] = &$columns;

        $em = $this->getDoctrine()->getManager();
        $rResult = $em->getRepository('UsuariosBundle:Usuario')->ajaxTable(
            $get,
            $filters,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('UsuariosBundle:Usuario')->getCount());
        $query = $em->getRepository('UsuariosBundle:Usuario')->ajaxTable($get, $filters, true);
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
