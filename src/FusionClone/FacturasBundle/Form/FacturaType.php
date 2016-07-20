<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 19/09/14
 * Time: 18:49
 */

namespace FusionClone\FacturasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class FacturaType extends AbstractType
{
    private $action;
    private $tipoForm;
    private $id;
    private $estadosChoices;
    private $monedasChoices;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formFactura')
            ->setAction($this->action)
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('importe', 'hidden')
            ->add('saldo', 'hidden')
            ->add(
                'codigo',
                'text',
                array('attr' => array('class' => 'form-control pull-right', 'style' => 'width:55%; '))
            )
            ->add(
                'fecha',
                'date',
                array(
                    'invalid_message' => 'La Fecha debe tener un formato válido',
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'fechaVenc',
                'date',
                array(
                    'invalid_message' => 'La Fecha de Vencimiento debe tener un formato válido',
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'moneda',
                'choice',
                array('choice_list' => $this->monedasChoices, 'attr' => array('class' => 'form-control'))
            )
            ->add(
                'tasa',
                'number',
                array(
                    'invalid_message' => 'La Tasa debe ser un número válido',
                    'precision' => 4,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'estado',
                'choice',
                array('choice_list' => $this->estadosChoices, 'attr' => array('class' => 'form-control'))
            )
            ->add(
                'terms',
                'textarea',
                array(
                    'required' => false,
                    'attr' => array('class' => 'form-control pull-right', 'style' => 'width:100%; ')
                )
            )
            ->add(
                'pie',
                'textarea',
                array(
                    'required' => false,
                    'attr' => array('class' => 'form-control pull-right', 'style' => 'width:100%; ')
                )
            )
            ->add(
                'factItems',
                'collection',
                array(
                    'allow_add' => true,
                    'allow_delete' => true,
                    'type' => new FacturaItemType(),
                    'by_reference' => false
                )
            )
            ->add(
                'factImps',
                'collection',
                array(
                    'allow_add' => true,
                    'allow_delete' => true,
                    'type' => new FacturaImpType(),
                    'by_reference' => false
                )
            )
            ->add('Guardar', 'submit', array('attr' => array('class' => 'btn btn-primary')))
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\FacturasBundle\Entity\Factura'
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
     * @param mixed $tipoForm
     */
    public function setTipoForm($tipoForm)
    {
        $this->tipoForm = $tipoForm;
    }


    /**
     * @param mixed $estadosChoices
     */
    public function setEstadosChoices($estadosChoices)
    {
        $this->estadosChoices = $estadosChoices;
    }

    /**
     * @param mixed $monedasChoices
     */
    public function setMonedasChoices($monedasChoices)
    {
        $this->monedasChoices = $monedasChoices;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formFactura';
    }
}