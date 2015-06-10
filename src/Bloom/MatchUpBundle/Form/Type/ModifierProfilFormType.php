<?php

namespace Bloom\MatchUpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Doctrine\ORM\EntityRepository;

class ModifierProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('poule', 'integer', array('required'  => false ))
            ->add('victoires', 'integer', array('required'  => false ))
            ->add('sets', 'integer', array('required'  => false ))

            ->add('user', 'bloom_selectionner_profil', array('mapped' => false));
        ;
    }

    public function getName()
    {
        return 'bloom_modifier_profil';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
        'data_class' => 'Bloom\UserBundle\Entity\User',
        'cascade_validation' => true,
        ));
    }
}