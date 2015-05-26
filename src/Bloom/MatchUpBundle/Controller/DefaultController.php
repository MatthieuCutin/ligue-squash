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

	    if ($user->getpoule() >0) {

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

		else {
			return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig');
		}
	
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

		//je récupère tous ceux qui étaient inscrit dans une poule
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listeActuelsParticipants = $repository->findActuelsParticipants();

		//je récupère ceux qui veulent participer
		$listeFutursParticipants = $repository->findFutursParticipants();

		for ($i=0; $i < count($listeFutursParticipants); $i++) { 
			$listeFutursParticipants[$i] -> setNouvellePoule(0);
		}

		//je les classe

		//je fais une boucle car je ne connais pas le nombre de poules établies

		for ($i=0; $i < count($listeActuelsParticipants) ; $i++) { 
			$listeActuelsParticipants[$i] -> setNouvellePoule( $listeActuelsParticipants[$i] -> getpoule() );
		}
		//j'amorce la boucle
		$i = 1;

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementParPoule = $repository->findByVicInPoule($i);

		$userManager = $this->get('fos_user.user_manager');

		while ( count($classementParPoule) >0 ) {

			for ($j = 1; $j <= 2; $j++) {

				$joueurAClasser = $classementParPoule[count($classementParPoule) - $j];
				$joueurAClasser -> setNouvellepoule($i - 1);

				$userManager->updateUser($joueurAClasser);

				$joueurAClasser = $classementParPoule[ $j - 1 ];
				$joueurAClasser -> setNouvellePoule($i + 1);

				$userManager->updateUser($joueurAClasser);
			}

			$i = $i+1;
			$classementParPoule = $repository->findByVicInPoule($i);
		}

		//je sors les anciens joueurs qui ne veulent pas faire la suivante
		$listeNoFutursParticipants = $repository->findNoFutursParticipants();

		for ($i=0; $i < count($listeNoFutursParticipants); $i++) { 
			$listeNoFutursParticipants[$i] -> setNouvellePoule(NULL);

			$userManager->updateUser($listeNoFutursParticipants[$i]);
		}

		//on obtient le classement des joueurs à la prochaine
		$NouveauClassementJoueurs = $repository->findByPouleAndVicAndSetsDESC();

		//on détermine le nombre de poules
		$NombreJoueursParPoule = $this->container->getParameter('NombreJoueursParPoule');

		$NombreJoueurs = count($listeFutursParticipants);
		$NombrePoules = floor($NombreJoueurs/$NombreJoueursParPoule);
		$NombreGrandesPoules = $NombreJoueurs % $NombrePoules;

		//je réinitialise la colonne poule
		$listeUsers = $repository->findAll();

		for ($i=0; $i < count($listeUsers); $i++) { 
			$listeUsers[$i] -> setPoule(NULL);
			$userManager->updateUser($listeUsers[$i]);
		}

		//je ventile dans les poules
		if ($NombreGrandesPoules > 0) {
			for ($i=1; $i <= $NombreGrandesPoules ; $i++) {
				for ($j=0; $j < $NombreJoueursParPoule+1 ; $j++) {

					$k = $j + ($i-1)*($NombreJoueursParPoule+1);

					$NouveauClassementJoueurs[$k] -> setPoule($i);
					$NouveauClassementJoueurs[$k] -> setVictoires(0);
					$NouveauClassementJoueurs[$k] -> setSets(0);					

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

		//je réinitialise la colonne nouvelle poule
		$listeUsers = $repository->findAll();

		for ($i=0; $i < count($listeUsers); $i++) { 
			$listeUsers[$i] -> setNouvellePoule(NULL);
			$userManager->updateUser($listeUsers[$i]);
		}

		//je réinitialise les tables de rencontre
		$em = $this->getDoctrine()
			->getManager();

		$connection = $em->getConnection();
		$platform   = $connection->getDatabasePlatform();
  
		$connection->executeUpdate($platform->getTruncateTableSQL('Rencontre', false /* whether to cascade */));

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

	public function ProfilAction($idAdversaire)
	{
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$adversaire = $repository->loadUserById($idAdversaire);

		return $this->render('BloomUserBundle:Profile:showAdversaire.html.twig', array(
            'user' => $adversaire
        ));
	}
}