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
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;


class FacturaImpType extends AbstractType
{
    private $action;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formFactImp')
            ->add('id', 'hidden')
            ->add('porcentaje', 'hidden')
            ->add('total', 'hidden')
            ->add(
                'antesImpItem',
                'choice',
                array('choice_list' => $this->getAntesImpItem(), 'attr' => array('class' => 'form-control'))
            )
            ->add('impuesto')
            ->getForm();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'FusionClone\FacturasBundle\Entity\FacturaImp'
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

    public function getAntesImpItem()
    {
        return new ChoiceList(array(true, false), array('Antes de Importe de Productos', 'Después de Importe de Productos'));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formFactImp';
    }
}