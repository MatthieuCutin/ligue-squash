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

class DefaultController extends Controller
{

	public function EntrerResultatAction(Request $request)
	{
		$rencontre = new Rencontre();
        $user = $this->container->get('security.context')->getToken()->getUser();
         $form = $this->createForm('bloom_adversaire_poule_score', $rencontre);

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      	$form->bind($request);

			if ($form->isValid()) {

				$test = $form->get('IdVainqueur')->getData();
				$scorePerdant = $form->get('Scoreperdant')->getData();
				$adversaireUsername = $form->get('User')->getData();

				$adversaireUsername = (string) $adversaireUsername;

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');
				$adversaire = $repository->LoadUserByUsername($adversaireUsername);

				$rencontre -> setScorePerdant($scorePerdant);

				if ($test == 0) {
					$rencontre -> setIdVainqueur($user -> getId());
					$rencontre -> setIdPerdant($adversaire -> getId());

					$user -> setVictoires($user -> getVictoires() + 1);
					$user -> setSets($user -> getSets() + 3);
					$adversaire -> setSets($adversaire -> getSets() + $scorePerdant);

					$user->addRencontre($rencontre);
					$userManager = $this->get('fos_user.user_manager');
	           		$userManager->updateUser($user);
				}
				elseif ($test == 1) {
					$rencontre -> setIdVainqueur($adversaire -> getId());
					$rencontre -> setIdPerdant($user -> getId());

					$adversaire -> setVictoires($adversaire -> getVictoires() + 1);
					$adversaire -> setSets($adversaire -> getSets() + 3);
					$user -> setSets($user -> getSets() + $scorePerdant);

					$adversaire->addRencontre($rencontre);
					$userManager = $this->get('fos_user.user_manager');
	           		$userManager->updateUser($adversaire);														
				}				

				$response = $this->forward('BloomMatchUpBundle:Default:AfficherPoule');

				return $response;
			}
	  	}

		return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
	    	'form' => $form->createView(),
	    	));
	
	}

	public function AfficherPouleAction( $NumeroPoule = 0 )
	{
		if ($NumeroPoule == 0) {
			$user = $this->container->get('security.context')->getToken()->getUser();

			if ($NumeroPoule = $user -> getpoule() > 0) {
				$NumeroPoule = $user -> getpoule();
			}
			else{
				$NumeroPoule = 1;
			}

			
		}

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listejoueurs = $repository->findAll();

		$NombreJoueursParPoule = 4;

		$NombreJoueurs = count($listejoueurs);
		$NombrePoules = floor($NombreJoueurs/$NombreJoueursParPoule);
		$NombreGrandesPoules = $NombreJoueurs % $NombrePoules;


		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementpoule = $repository->findByVicInPoule($NumeroPoule);

		for ($i=0; $i < count($classementpoule) ; $i++) {
			$joueursId[$i] = $classementpoule[$i] -> getId();
		}

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomMatchUpBundle:Rencontre');
		$listerencontres = $repository->findAll();

		$rencontresIdVainqueur[0] = 0;
		$rencontresIdPerdant[0] =0;
		$scorePerdant[0] =0;

		for ($i=0; $i < count($listerencontres) ; $i++) {
			$rencontresIdVainqueur[$i] = $listerencontres[$i] -> getIdVainqueur();
			$rencontresIdPerdant[$i] = $listerencontres[$i] -> getIdPerdant();
			$scorePerdant[$i] = $listerencontres[$i] -> getScorePerdant();
		}

	    return $this->render('BloomMatchUpBundle:Default:classementpoule.html.twig', array(
	    	'classementpoule' => $classementpoule,
	    	'NumeroPoule'     => $NumeroPoule,
	    	'NombrePoules'    => $NombrePoules,
	    	'rencontresIdVainqueur' => $rencontresIdVainqueur,
	    	'rencontresIdPerdant'   => $rencontresIdPerdant,
	    	'scorePerdant'          => $scorePerdant, 
	    	'joueursId'       => $joueursId  
	    	));

	}

	public function GenererPouleAction()
	{
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listejoueurs = $repository->findAll();

		$NombreJoueursParPoule = 4;

		$NombreJoueurs = count($listejoueurs);
		$NombrePoules = floor($NombreJoueurs/$NombreJoueursParPoule);
		$NombreGrandesPoules = $NombreJoueurs % $NombrePoules;


		for ($i=0; $i < count($listejoueurs) ; $i++) { 
			$listejoueurs[$i] -> setNouvellePoule( $listejoueurs[$i] -> getpoule() );
		}

		for ($i = 1; $i <= $NombrePoules; $i++) {
		
			$repository = $this->getDoctrine()
			->getManager()
			->getRepository('BloomUserBundle:User');
			$classementpoule = $repository->findByVicInPoule($i);

			for ($j = 1; $j <= 2; $j++) {

				$joueurAClasser = $classementpoule[count($classementpoule) - $j];
				$joueurAClasser -> setNouvellepoule($i - 1);

				$userManager = $this->get('fos_user.user_manager');
				$userManager->updateUser($joueurAClasser);

				$joueurAClasser = $classementpoule[ $j - 1 ];
				$joueurAClasser -> setNouvellePoule($i + 1);

				$userManager = $this->get('fos_user.user_manager');
				$userManager->updateUser($joueurAClasser);
			}
		}

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$NouveauClassementJoueurs = $repository->FindByPouleAndVicAndSetsDESC();

		if ($NombreGrandesPoules > 0) {
			for ($i=1; $i <= $NombreGrandesPoules ; $i++) {
				for ($j=0; $j < $NombreJoueursParPoule+1 ; $j++) {

					$k = $j + ($i-1)*($NombreJoueursParPoule+1);

					$NouveauClassementJoueurs[$k] -> setPoule($i);
					$NouveauClassementJoueurs[$k] -> setVictoires(0);
					$NouveauClassementJoueurs[$k] -> setSets(0);					

					$userManager = $this->get('fos_user.user_manager');
					$userManager->updateUser($NouveauClassementJoueurs[$k]);
				}		
			}
		}

		for ($i=1; $i <= $NombrePoules - $NombreGrandesPoules ; $i++) {
			for ($j=0; $j < $NombreJoueursParPoule ; $j++) {

				$k = $j + $NombreGrandesPoules*($NombreJoueursParPoule + 1) + ($i-1)*($NombreJoueursParPoule);

				$NouveauClassementJoueurs[$k] -> setPoule($i + $NombreGrandesPoules);
				$NouveauClassementJoueurs[$k] -> setVictoires(0);
				$NouveauClassementJoueurs[$k] -> setSets(0);				

				$userManager->updateUser($NouveauClassementJoueurs[$k]);
			}		
		}

		$em = $this->getDoctrine()
			->getManager();

		$connection = $em->getConnection();
		$platform   = $connection->getDatabasePlatform();
  
		$connection->executeUpdate($platform->getTruncateTableSQL('user_rencontre', true /* whether to cascade */));

		$connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
		$truncateSql = $platform->getTruncateTableSQL('rencontre');
		$connection->executeUpdate($truncateSql);
		$connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

	    $response = $this->forward('BloomMatchUpBundle:Default:homepage');

	    return $response;

	}

	public function HomepageAction()
	{

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listejoueurs = $repository->FindByPouleAndVicAndSets();

		return $this->render('BloomMatchUpBundle:Default:homepage.html.twig', array(
			    	'listejoueurs'    => $listejoueurs
			    	));
	}

	public function IndexAction()
	{
		return $this->render('BloomMatchUpBundle:Default:index.html.twig');
	}

	public function MatchupAction()
	{

		$user = $this->container->get('security.context')->getToken()->getUser();

		//Je récupère les joueurs de la poule

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$joueurs = $repository->FindByPouleAndName($user -> getpoule());

		for ($i=0; $i < count($joueurs); $i++) { 
			if ($joueurs[$i] !== $user) {
				$joueursPasses[$i] = $joueurs[$i];
			}
		}

	    return $this->render('BloomMatchUpBundle:Default:matchup.html.twig', array(
	    	'user'          => $user,
	    	'joueursPasses' => $joueursPasses
	    	));
	}

	public function ProfilAction()
	{
		return $this->render('BloomMatchUpBundle:Default:homepage.html.twig');
	}
}