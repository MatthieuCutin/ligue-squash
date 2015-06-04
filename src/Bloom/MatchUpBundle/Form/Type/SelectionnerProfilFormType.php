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

class SelectionnerProfilFormType extends AbstractType
{

    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ;

        // grab the user, do a quick sanity check that one exists
        $user = $this->securityContext->getToken()->getUser();
        if (!$user) {
            throw new \LogicException(
                'The AdversairePouleFormType cannot be used without an authenticated user!'
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

                        return $er->findAllForm();
                    },
                );

                $form->add('username', 'entity', $formOptions);
            }
        );
    }

    public function getName()
    {
        return 'bloom_selectionner_profil';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bloom\UserBundle\Entity\User',
        ));
    }
}