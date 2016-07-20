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

class NomMoneType extends AbstractType
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
            ->add('codigo', 'text', array('attr' => array('class' => 'form-control')))
            ->add('descripcion', 'text', array('attr' => array('class' => 'form-control')))
            ->add('simbolo', 'text', array('attr' => array('class' => 'form-control')))
            ->add(
                'ubicaSimbol',
                'choice',
                array('choice_list' => $this->getUbicaSimbolChoices(), 'attr' => array('class' => 'form-control'))
            )
            ->add('signDecimal', 'text', array('attr' => array('class' => 'form-control')))
            ->add('signMillares', 'text', array('attr' => array('class' => 'form-control')))
            ->add(
                'tasa',
                'number',
                array(
                    'invalid_message' => 'La Tasa debe ser un número válido',
                    'precision' => 7,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\NomencladoresBundle\Entity\NomMone'
            )
        );
    }

    public function getUbicaSimbolChoices()
    {
        return new ChoiceList(array(true, false), array('Antes de Importe', 'Después de Importe'));
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
        return 'formMoneda';
    }
}