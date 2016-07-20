<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:55
 */

namespace FusionClone\NomencladoresBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NomImpType extends AbstractType
{
    private $action;
    private $id;
    private $tipoForm;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formCliente')
            ->setAction($this->action)
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add('nombre', 'text', array('attr' => array('class' => 'form-control')))
            ->add('porcentaje', 'number', array('invalid_message' => 'El porcentaje debe ser un número válido', 'attr' => array('class' => 'form-control')))
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\NomencladoresBundle\Entity\NomImp'
            )
        );
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
        return 'formImp';
    }
}