<?php
/**
 * Created by PhpStorm.
 * User: osmany.torres
 * Date: 08/07/2015
 * Time: 11:23
 */

namespace FusionClone\NomencladoresBundle\Controller;


use Doctrine\DBAL\DBALException;
use FusionClone\NomencladoresBundle\Entity\NomProd;
use FusionClone\NomencladoresBundle\Form\NomProdType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductoController
 * @package FusionClone\NomencladoresBundle\Controller
 */
class ProductoController extends Controller
{
    /**
     * @return Response
     */
    public function listProdAction()
    {
        $peticion = $this->getRequest();
        $session = $peticion->getSession();

        $session->remove('filters');

        return $this->render('NomencladoresBundle:Producto:listProd.html.twig');
    }

    /**
     * @return Response
     */
    public function createProdAction()
    {
        $producto = new NomProd();

        $productoType = new NomProdType();
        $productoType->setTipoForm('add');
        $productoType->setAction($this->generateUrl('nomencladores_saveProd'));
        $formProd = $this->createForm($productoType, $producto);

        return $this->render(
            'NomencladoresBundle:Producto:createProd.html.twig',
            array('formProd' => $formProd->createView())
        );
    }

    /**
     * @param $id
     * @return Response
     */
    public function editProdAction($id)
    {
        $producto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomProd')->find($id);

        $productoType = new NomProdType();
        $productoType->setId($id);
        $productoType->setTipoForm('edit');
        $productoType->setAction($this->generateUrl('nomencladores_saveProd'));
        $formProd = $this->createForm($productoType, $producto);

        return $this->render(
            'NomencladoresBundle:Producto:createProd.html.twig',
            array('formProd' => $formProd->createView())
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function saveProdAction()
    {
        $peticion = $this->getRequest();
        $form = $peticion->get('formProd');
        if ($form['tipoForm'] == 'add') {
            $producto = new NomProd();
        } elseif ($form['tipoForm'] == 'edit') {
            $producto = $this->getDoctrine()->getManager()->getRepository('NomencladoresBundle:NomProd')->find(
                $form['id']
            );
        }

        $productoType = new NomProdType();
        $productoType->setAction($this->generateUrl('nomencladores_saveProd'));
        $productoType->setTipoForm($form['tipoForm']);

        $formProd = $this->createForm($productoType, $producto);

        $formProd->handleRequest($peticion);
        if ($formProd->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($producto);
            $em->flush();

            if ($form['tipoForm'] == 'add') {
                $this->get('session')->getFlashBag()->add(
                    'info_add',
                    'Producto creado exitosamente'
                );
            } elseif ($form['tipoForm'] == 'edit') {
                $this->get('session')->getFlashBag()->add(
                    'info_edit',
                    'Producto actualizado exitosamente'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listProd'));

        }

        return $this->render(
            'NomencladoresBundle:Producto:createProd.html.twig',
            array('formProd' => $formProd->createView())
        );
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteProdAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $producto = $em->find('NomencladoresBundle:NomProd', $id);

        if (!$producto) {
            $this->createNotFoundException('No se encontró el Producto.');
        } else {
            try {
                $em->remove($producto);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'info_delete',
                    'Producto eliminado exitosamente'
                );
            } catch (DBALException $e) {
                $this->get('session')->getFlashBag()->add(
                    'info_error',
                    'No se ha podido eliminar el producto pues está siendo utilizado'
                );
            }

            return $this->redirect($this->generateUrl('nomencladores_listProd'));
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
        $columns = array('id', 'nombre', 'descripcion', 'precio');
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
        $rResult = $em->getRepository('NomencladoresBundle:NomProd')->ajaxTable(
            $get,
            $filters,
            true
        )->getArrayResult();

        /* Data set length after filtering without limiting*/
        $iRecordsTotal = intval($em->getRepository('NomencladoresBundle:NomProd')->getCount());
        $query = $em->getRepository('NomencladoresBundle:NomProd')->ajaxTable($get, $filters, true);
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
                    case 'precio':
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

    /**
     * @return Response
     */
    public function prodsAjaxAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $prodsOutput = array();

        if (!is_null($request->get('search')) and $request->get('search') != "") {
            $prods = $em->getRepository('NomencladoresBundle:NomProd')->findByName($request->get('search'));
            foreach ($prods as $prod) {
                $prodsOutput[$prod->getId()] = $prod->getNombre();
            }
        } else {
            if (!is_null($request->get('id')) and $request->get('id') != "") {
                $prod = $em->getRepository('NomencladoresBundle:NomProd')->find($request->get('id'));
                $prodsOutput[$prod->getId()] = $prod->getNombre();
            } else {
                $prods = $em->getRepository('NomencladoresBundle:NomProd')->findAll();
                foreach ($prods as $prod) {
                    $prodsOutput[$prod->getId()] = $prod->getNombre();
                }
            }
        }

        return new Response(json_encode($prodsOutput));
    }

    /**
     * @return Response
     */
    public function createProdViaAjaxAction()
    {
        $request = $this->getRequest();
        $product = $request->get('product');
        $newProduct = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomProd'
        )->findByName(
            $product
        );
        if (count($newProduct) == 0) {
            $newProduct = new NomProd();
            $newProduct->setNombre($product);
            $newProduct->setDescripcion($product);
            $newProduct->setPrecio(0);
            try {
                $this->getDoctrine()->getManager()->persist($newProduct);
                $this->getDoctrine()->getManager()->flush();

                return new Response(
                    json_encode(
                        array(
                            'id' => $newProduct->getId(),
                            'nombre' => $newProduct->getNombre(),
                            'descripcion' => $newProduct->getDescripcion()
                        )
                    )
                );
            } catch (DBALException $e) {
                return new Response(json_encode(-1));
            }
        }

        return new Response(json_encode(-1));

    }

    /**
     * @return Response
     */
    public function getProdDescAction()
    {
        $request = $this->getRequest();
        $product = $request->get('product');
        $newProduct = $this->getDoctrine()->getManager()->getRepository(
            'NomencladoresBundle:NomProd'
        )->find($product);
        if (!is_null($newProduct)) {
            return new Response(
                json_encode(
                    array(
                        'descripcion' => $newProduct->getDescripcion()
                    )
                )
            );
        }

        return new Response(json_encode(-1));

    }
}