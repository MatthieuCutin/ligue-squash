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

class AdversairePouleScoreFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idVainqueur', 'choice', array(
                'choices'   => array('0' => 'J\'ai gagné', '1' => 'J\'ai perdu' ),
                'required'  => true,
                ))
            ->add('scorePerdant', 'choice', array(
                'choices'   => array('0' => '0', '1' => '1', '2' => '2'),
                'required'  => true,
                ))

            ->add('user', 'bloom_adversaire_poule', array('mapped' => false));
        ;
    }

    public function getName()
    {
        return 'bloom_adversaire_poule_score';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
        'data_class' => 'Bloom\MatchUpBundle\Entity\Rencontre',
        'cascade_validation' => true,
        ));
    }
}