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

class NomTdocConfType extends AbstractType
{
    private $action;
    private $id;
    private $tipoForm;
    private $tdocChoices;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formCliente')
            ->setAction($this->action)
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add(
                'consecutivo',
                'number',
                array(
                    'invalid_message' => 'El siguiente ID debe ser un número',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'cantDigCons',
                'number',
                array(
                    'invalid_message' => 'El margen izquierdo debe ser un número',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('descripcion', 'text', array('attr' => array('class' => 'form-control')))
            ->add('prefijo', 'text', array('attr' => array('class' => 'form-control')))
            ->add(
                'tdoc',
                'choice',
                array(
                    'choice_list' => $this->tdocChoices,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'anno',
                'choice',
                array(
                    'choice_list' => new ChoiceList(array(true, false), array('Si', 'No')),
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'mes',
                'choice',
                array(
                    'choice_list' => new ChoiceList(array(true, false), array('Si', 'No')),
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
                'data_class' => 'FusionClone\NomencladoresBundle\Entity\NomTdocConf'
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
     * @param mixed $tdocChoices
     */
    public function setTdocChoices($tdocChoices)
    {
        $this->tdocChoices = $tdocChoices;
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formGrupo';
    }
}