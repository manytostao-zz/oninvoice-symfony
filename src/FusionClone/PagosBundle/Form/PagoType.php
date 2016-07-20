<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 19/09/14
 * Time: 18:49
 */

namespace FusionClone\PagosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;


class PagoType extends AbstractType
{
    private $action;
    private $factId;
    private $tipoForm;
    private $metodos;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formPago')
            ->setAction($this->action)
            ->add('factId', 'hidden', array('mapped' => false, 'data' => $this->factId))
            ->add('tipoForm', 'hidden', array('mapped' => false, 'data' => $this->tipoForm))
            ->add('id', 'hidden')
            ->add('metodo', 'choice', array('empty_value' => false, 'choice_list'=>$this->metodos))
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
            ->add('nota', 'textarea')
            ->add('importe')
            ->add('Guardar', 'submit')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\PagosBundle\Entity\Pago'
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
     * @param mixed $factId
     */
    public function setFactId($factId)
    {
        $this->factId = $factId;
    }

    /**
     * @param mixed $tipoForm
     */
    public function setTipoForm($tipoForm)
    {
        $this->tipoForm = $tipoForm;
    }

    /**
     * @param mixed $metodos
     */
    public function setMetodos($metodos)
    {
        $this->metodos = $metodos;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formPago';
    }
}