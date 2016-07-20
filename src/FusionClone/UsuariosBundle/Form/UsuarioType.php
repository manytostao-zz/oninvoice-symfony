<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 16/09/14
 * Time: 19:55
 */

namespace FusionClone\UsuariosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioType extends AbstractType
{
    private $id;
    private $clientesChoices;
    private $dataClie;
    private $action;
    private $tipoForm;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('id', 'formUsua')
            ->setAction($this->action)
            ->add('id', 'hidden', array('data' => $this->id, 'mapped' => false))
            ->add('tipoForm', 'hidden', array('data' => $this->tipoForm, 'mapped' => false))
            ->add(
                'cliente',
                'choice',
                array(
                    'required' => false,
                    'choice_list' => $this->clientesChoices,
                    'data' => $this->dataClie,
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add('nombre', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add(
                'direccion',
                'textarea',
                array('required' => false, 'attr' => array('class' => 'form-control', 'rows' => 4))
            )
            ->add('email', 'email', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('fax', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('telefono', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('movil', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('webpage', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('compannia', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add(
                'password',
                'repeated',
                array(
                    'type' => 'password',
                    'invalid_message' => 'Las dos contraseñas deben coincidir',
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
                'data_class' => 'FusionClone\UsuariosBundle\Entity\Usuario'
            )
        );
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setClientesChoices($clientesChoices)
    {
        $this->clientesChoices = $clientesChoices;
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
     * @param mixed $dataClie
     */
    public function setDataClie($dataClie)
    {
        $this->dataClie = $dataClie;
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'formUsua';
    }
}