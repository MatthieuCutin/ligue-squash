<?php

namespace Bloom\MatchUpBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Doctrine\ORM\EntityRepository;

class AdversairePouleFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ;

        $session = new Session();
        $user = $session -> get('profil');
        if (!$user) {
            throw new \LogicException(
                'Aucun utilisateur authentifié.'
            );
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($user) {
                $form = $event->getForm();

                $formOptions = array(
                    'class' => 'Bloom\UserBundle\Entity\User',
                    'property' => 'username',
                    'query_builder' => function (EntityRepository $er) use ($user) {

                        return $er->findByVicInPouleForm($user->getPoule(), $user->getUsername());
                    },
                );

                $form->add('username', 'entity', $formOptions);
            }
        );
    }

    public function getName()
    {
        return 'bloom_adversaire_poule';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bloom\UserBundle\Entity\User',
        ));
    }
}