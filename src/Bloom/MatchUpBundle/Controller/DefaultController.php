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

				$repositoryUser = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');
				$adversaire = $repositoryUser->LoadUserByUsername($adversaireUsername);

				$repositoryRencontre = $this->getDoctrine()
				->getManager()
				->getRepository('BloomMatchUpBundle:Rencontre');


				$em = $this->getDoctrine()->getEntityManager();

				if ($test == 0) {
					$rencontre -> setIdVainqueur($user -> getId());
					$rencontre -> setIdPerdant($adversaire -> getId());	

					$user -> setVictoires($user -> getVictoires() + 1);
					$user -> setSets($user -> getSets() + 3);
					$adversaire -> setSets($adversaire -> getSets() + $scorePerdant);

					$rencontreTest1 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdVainqueur(), $rencontre->getIdPerdant());
					$rencontreTest2 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdPerdant(), $rencontre->getIdVainqueur());


					if ($rencontreTest1) {

						$user -> setVictoires($user -> getVictoires() - 1);
						$user -> setSets($user -> getSets() - 3);
						$scorePerdantPrecedentMatch = $rencontreTest1 -> getScorePerdant();
						$adversaire -> setSets($adversaire -> getSets() - $scorePerdantPrecedentMatch);

						$rencontreTest1 -> setScorePerdant($scorePerdant);
						$rencontreTest1 -> setIdVainqueur($user -> getId());
						$rencontreTest1 -> setIdPerdant($adversaire -> getId());						
						$em->flush();


					}
					elseif ($rencontreTest2) {

						$adversaire -> setVictoires($adversaire -> getVictoires() - 1);
						$adversaire -> setSets($adversaire -> getSets() - 3);
						$scorePerdantPrecedentMatch = $rencontreTest2 -> getScorePerdant();
						$user -> setSets($user -> getSets() - $scorePerdantPrecedentMatch);

						$rencontreTest2 -> setScorePerdant($scorePerdant);
						$rencontreTest2 -> setIdVainqueur($user -> getId());
						$rencontreTest2 -> setIdPerdant($adversaire -> getId());						
						$em->flush();					
					}
					else {
						$rencontre -> setScorePerdant($scorePerdant);						
						$user->addRencontre($rencontre);
						$userManager = $this->get('fos_user.user_manager');
		           		$userManager->updateUser($user);
					}
				}

				elseif ($test == 1) {
					$rencontre -> setIdVainqueur($adversaire -> getId());
					$rencontre -> setIdPerdant($user -> getId());

					$adversaire -> setVictoires($adversaire -> getVictoires() + 1);
					$adversaire -> setSets($adversaire -> getSets() + 3);
					$user -> setSets($user -> getSets() + $scorePerdant);

					$rencontreTest1 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdVainqueur(), $rencontre->getIdPerdant());
					$rencontreTest2 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdPerdant(), $rencontre->getIdVainqueur());


					if ($rencontreTest1) {

						$adversaire -> setVictoires($adversaire -> getVictoires() - 1);
						$adversaire -> setSets($adversaire -> getSets() - 3);
						$scorePerdantPrecedentMatch = $rencontreTest1 -> getScorePerdant();
						$user -> setSets($user -> getSets() - $scorePerdantPrecedentMatch);

						$rencontreTest1 -> setScorePerdant($scorePerdant);
						$rencontreTest1 -> setIdVainqueur($adversaire -> getId());
						$rencontreTest1 -> setIdPerdant($user -> getId());						
						$em->flush();					
					}
					elseif ($rencontreTest2) {

						$user -> setVictoires($user -> getVictoires() - 1);
						$user -> setSets($user -> getSets() - 3);
						$scorePerdantPrecedentMatch = $rencontreTest2 -> getScorePerdant();
						$adversaire -> setSets($adversaire -> getSets() - $scorePerdantPrecedentMatch);

						$rencontreTest2 -> setScorePerdant($scorePerdant);
						$rencontreTest2 -> setIdVainqueur($adversaire -> getId());
						$rencontreTest2 -> setIdPerdant($user -> getId());						
						$em->flush();					
					}
					else {
						$rencontre -> setScorePerdant($scorePerdant);						
						$user->addRencontre($rencontre);
						$userManager = $this->get('fos_user.user_manager');
		           		$userManager->updateUser($user);
					}								
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

		$NombreJoueursParPoule = $this->container->getParameter('NombreJoueursParPoule');

		$NombreJoueurs = count($listejoueurs);
		$NombrePoules = floor($NombreJoueurs/$NombreJoueursParPoule); 
		if ($NombrePoules ==0) {return $this->render('BloomMatchUpBundle:Default:classementpoule.html.twig');}
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

		$NombreJoueursParPoule = $this->container->getParameter('NombreJoueursParPoule');

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
  
		$connection->executeUpdate($platform->getTruncateTableSQL('rencontre', false /* whether to cascade */));

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

	public function mailAction()
	{
		$name = 'coucoutoi';
	    $message = \Swift_Message::newInstance()
	        ->setSubject('Hello Email2')
	        ->setFrom('test@gspevents.fr')
	        ->setTo('matthieu.cutin@gmail.com')
	        ->setBody($this->renderView('BloomMatchUpBundle:Default:email.txt.twig', array('name' => $name)))
	    ;
	    $this->get('mailer')->send($message);

	    $response = $this->forward('BloomMatchUpBundle:Default:homepage');

	    return $response;

	}

	public function MatchupAction()
	{

		$user = $this->container->get('security.context')->getToken()->getUser();

		//Je récupère les joueurs de la poule
		if ($user->getpoule() !== NULL) {
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
		else {
		    return $this->render('BloomMatchUpBundle:Default:matchup.html.twig');
		}
	}

	public function ProfilAction()
	{
		return $this->render('BloomMatchUpBundle:Default:homepage.html.twig');
	}
}