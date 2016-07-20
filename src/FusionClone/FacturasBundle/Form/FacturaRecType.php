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
use FusionClone\OtrosBundle\OverridenClasses\ChoiceListStringValue;


class FacturaRecType extends AbstractType
{
    private $action;
    private $tipoForm;
    private $facBase;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formFactRec')
            ->add('id', 'hidden', array('mapped' => false))
            ->add('facBase', 'hidden', array('mapped' => false, 'data' => $this->facBase))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->setAction($this->action)

            ->add(
                'cada',
                'number',
                array(
                    'invalid_message' => 'La frecuencia de la recurrencia debe ser un número válido',
                    'required' => false,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'intervalo',
                'choice',
                array(
                    'empty_value' => false,
                    'mapped' => false,
                    'expanded' => true,
                    'required' => false,
                    'choice_list' => new ChoiceListStringValue(array(
                            'Día(s)',
                            'Semana(s)',
                            'Mes(es)',
                            'Año(s)'
                        ), array(
                            'Día(s)',
                            'Semana(s)',
                            'Mes(es)',
                            'Año(s)'
                        ))
                )
            )
            ->add(
                'fechaIni',
                'date',
                array(
                    'invalid_message' => 'La Fecha debe tener un formato válido',
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'fechaFin',
                'date',
                array(
                    'invalid_message' => 'La Fecha debe tener un formato válido',
                    'widget' => 'single_text',
                    'format' => 'MM/dd/yyyy',
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
                'data_class' => 'FusionClone\FacturasBundle\Entity\FacturaRec'
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
     * @param mixed $facBase
     */
    public function setFacBase($facBase)
    {
        $this->facBase = $facBase;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formRec';
    }
}