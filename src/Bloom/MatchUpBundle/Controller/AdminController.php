<?php

namespace Bloom\MatchUpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Bloom\UserBundle\Entity\User;
use Bloom\MatchUpBundle\Entity\Rencontre;
use Bloom\MatchUpBundle\Form\Type\AdversairePouleFormType;
use Bloom\MatchUpBundle\Form\Type\AdversairePouleScoreFormType;
use Symfony\Component\DependencyInjection\ContainerAware;

class AdminController extends Controller
{
	public function DashBoardAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:dashBoard.html.twig');
	}

	public function AjouterProfilAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:ajouterProfil.html.twig');
	}

	public function SupprimerProfilAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:supprimerProfil.html.twig');
	}

	public function ModifierProfilAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:modifierProfil.html.twig');
	}

	public function ModifierResultatAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:modifierResultat.html.twig');
	}
}