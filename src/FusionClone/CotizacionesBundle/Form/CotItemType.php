<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 19/09/14
 * Time: 18:49
 */

namespace FusionClone\CotizacionesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CotItemType extends AbstractType
{
    private $action;
    private $products;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formCotItem')
            ->add('id', 'hidden')
            ->add('total', 'hidden')
            ->add('producto')
            ->add(
                'descripcion',
                'textarea',
                array('required' => false, 'attr' => array('class' => 'form-control'))
            )
            ->add(
                'cantidad',
                'number',
                array(
                    'invalid_message' => 'La Cantidad del Producto debe ser un número válido',
                    'required' => false,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'precio',
                'number',
                array(
                    'invalid_message' => 'El Precio del Producto debe ser un número válido',
                    'required' => false,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('impuesto')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\CotizacionesBundle\Entity\CotItem'
            )
        );
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formCotItem';
    }
}