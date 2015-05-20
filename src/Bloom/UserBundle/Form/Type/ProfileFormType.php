<?php

namespace Bloom\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildUserForm($builder, $options);

        $builder->add('current_password', 'password', array(
            'label' => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => new UserPassword(),
        ));
    }
    
    /**
     * Builds the embedded form representing the user.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('age', null, array('label' => 'Age', 'required' => false))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('tel', null, array('label' => 'Téléphone', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('participation', 'checkbox', array('label' => 'Je participe à la prochaine poule', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('file', 'file', array('label' => 'Photo de profil', 'translation_domain' => 'FOSUserBundle', 'required' => false))            
            ->add('lundi', null, array('label' => 'Disponibilité le lundi', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('mardi', null, array('label' => 'Disponibilité le mardi', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('mercredi', null, array('label' => 'Disponibilité le mercredi', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('jeudi', null, array('label' => 'Disponibilité le jeudi', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('vendredi', null, array('label' => 'Disponibilité le vendredi', 'translation_domain' => 'FOSUserBundle', 'required' => false))
            ->add('samedi', null, array('label' => 'Disponibilité le samedi', 'translation_domain' => 'FOSUserBundle', 'required' => false));
        ;
    }

    public function getParent()
    {
        return 'fos_user_profile';
    }

    public function getName()
    {
        return 'bloom_user_profile';
    }


}
