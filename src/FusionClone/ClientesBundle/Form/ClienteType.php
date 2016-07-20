<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:55
 */

namespace FusionClone\ClientesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClienteType extends AbstractType
{
    private $id;
    private $moneChoices;
    private $action;
    private $tipoForm;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formCliente')
            ->setAction($this->action)
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add('nombre', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add(
                'direccion',
                'textarea',
                array('required' => false, 'attr' => array('class' => 'form-control', 'rows' => 4))
            )
            ->add('email', 'email', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('fax', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('telefono', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('movil', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('webpage', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add(
                'defMone',
                'choice',
                array('choice_list' => $this->moneChoices, 'attr' => array('class' => 'form-control'))
            )
            ->add('activo')
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\ClientesBundle\Entity\Cliente'
            )
        );
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setMoneChoices($moneChoices)
    {
        $this->moneChoices = $moneChoices;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setTipoForm($tipoForm)
    {
        $this->tipoForm = $tipoForm;
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formCliente';
    }
}