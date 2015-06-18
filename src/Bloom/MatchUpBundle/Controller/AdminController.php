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
	public function dashBoardAction()
	{
		return $this->render('BloomMatchUpBundle:Admin:dashBoard.html.twig');
	}

	public function supprimerProfilAction()
	{

	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}

		$profil = new user;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm('bloom_selectionner_profil', $profil);

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      	$form->bind($request);

			if ($form->isValid()) {

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');

				$profilUsername = $form->getData();

				$profilUsername = (string) $profilUsername;

				$profil = $repository->loadUserByUsername($profilUsername);

				$em = $this->getDoctrine()->getManager();
				$em->remove($profil);
				$em->flush();

				return $this->render('BloomMatchUpBundle:Admin:supprimerProfil.html.twig');
			}
		}

		return $this->render('BloomMatchUpBundle:Admin:supprimerProfil.html.twig', array(
			'form' => $form->createView(),
			));

	}

	public function modifierProfilAction()
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}

		$profil = new user;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm('bloom_modifier_profil', $profil);

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      	$form->bind($request);

			if ($form->isValid()) {

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');

				$em = $this->getDoctrine()->getManager();

				$profilUsername = $form->get('user')->getData();
				$nouvellesVictoires = $form->get('victoires')->getData();
				$nouvellePoule = $form->get('poule')->getData();
				$nouveauxSets = $form->get('sets')->getData();

				$profilUsername = (string) $profilUsername;

				$profil = $repository->loadUserByUsername($profilUsername);

				if ($nouvellesVictoires !== NULL) {
					$profil -> setVictoires($nouvellesVictoires);	
				}

				if ($nouvellePoule !==NULL) {
					$profil -> setPoule($nouvellePoule);
				}

				if ($nouveauxSets !== NULL) {
					$profil -> setSets($nouveauxSets);
				}

				$em->flush();

				return $this->render('BloomMatchUpBundle:Admin:modifierProfil.html.twig');
			}
		}
		return $this->render('BloomMatchUpBundle:Admin:modifierProfil.html.twig', array(
			'form' => $form->createView(),
			));
	}

	public function selectionnerProfilAction()
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}

		$profil = new User;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm('bloom_selectionner_profil', $profil);

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      	$form->bind($request);

			if ($form->isValid()) {

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');

				$profilUsername = $form->getData();

				$profilUsername = (string) $profilUsername;

				$profil = $repository->LoadUserByUsername($profilUsername);
				$idProfil = $profil -> getId();

				$url = $this->generateUrl('bloom_match_up_admin_modifier_resultat', array('idProfil' => $idProfil));

				return $this->redirect($url);
			}
		}

		return $this->render('BloomMatchUpBundle:Admin:selectionnerProfil.html.twig', array(
			'form' => $form->createView(),
			));
	}

	public function modifierResultatAction($idProfil, Request $request)
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}
		
		//attention ici $user ne désigne pas l'utilisateur mais le profil selectionné juste avant
		//c'est pour pouvoir réutiliser le code que je n'ai pas changé $user, c'est pas hyper propre il faudra changer à l'occaz
        $repositoryUser = $this->getDoctrine()
					->getManager()
					->getRepository('BloomUserBundle:User');
		$user = $repositoryUser->loadUserById($idProfil);

		$session = new Session();
		$session -> set('profil', $user);

		$rencontre = new Rencontre();					
        $form = $this->createForm('bloom_adversaire_poule_score', $rencontre);

	    $request = $this->get('request');

		    if ($request->getMethod() == 'POST') {

		      	$form->bind($request);

				if ($form->isValid()) {

					//Je récupère les données du formulaire
					$test = $form->get('idVainqueur')->getData();
					$scorePerdant = $form->get('scorePerdant')->getData();
					$adversaireUsername = $form->get('user')->getData();

					$adversaireUsername = (string) $adversaireUsername;

					//je récupère l'objet adversaire au complet
					$repositoryUser = $this->getDoctrine()
					->getManager()
					->getRepository('BloomUserBundle:User');
					$adversaire = $repositoryUser->loadUserByUsername($adversaireUsername);

					$repositoryRencontre = $this->getDoctrine()
					->getManager()
					->getRepository('BloomMatchUpBundle:Rencontre');


					$em = $this->getDoctrine()->getManager();

					//la variable test indique le vainqueur : 0-> user a gagné , 1-> adversaire a gagné
					if ($test == 0) {
						$rencontre -> setIdVainqueur($user -> getId());
						$rencontre -> setIdPerdant($adversaire -> getId());

						$user -> setVictoires($user -> getVictoires() + 1);
						$user -> setSets($user -> getSets() + 3);
						$adversaire -> setSets($adversaire -> getSets() + $scorePerdant);

						//je cherche s'il existe déjà une rencontre entre ces deux joueurs
						$rencontreTest1 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdVainqueur(), $rencontre->getIdPerdant());
						$rencontreTest2 = $repositoryRencontre->findOneByRencontreByIdVainqueurAndIdPerdant($rencontre->getIdPerdant(), $rencontre->getIdVainqueur());


						if ($rencontreTest1) { //Une rencontre existe et user avait gagné

							//J'annule' les valeurs des victoires et sets de la précédente rencontre
							$user -> setVictoires($user -> getVictoires() - 1);
							$user -> setSets($user -> getSets() - 3);
							$scorePerdantPrecedentMatch = $rencontreTest1 -> getScorePerdant();
							$adversaire -> setSets($adversaire -> getSets() - $scorePerdantPrecedentMatch);

							//Je rentre les nouvelles valeurs
							$rencontreTest1 -> setScorePerdant($scorePerdant);
							$rencontreTest1 -> setIdVainqueur($user -> getId());
							$rencontreTest1 -> setIdPerdant($adversaire -> getId());						
							$em->flush();


						}
						elseif ($rencontreTest2) { //Une rencontre existe et adversaire avait gagné

							$adversaire -> setVictoires($adversaire -> getVictoires() - 1);
							$adversaire -> setSets($adversaire -> getSets() - 3);
							$scorePerdantPrecedentMatch = $rencontreTest2 -> getScorePerdant();
							$user -> setSets($user -> getSets() - $scorePerdantPrecedentMatch);

							$rencontreTest2 -> setScorePerdant($scorePerdant);
							$rencontreTest2 -> setIdVainqueur($user -> getId());
							$rencontreTest2 -> setIdPerdant($adversaire -> getId());						
							$em->flush();					
						}
						else { //Aucune rencontre n'existait
							$rencontre -> setScorePerdant($scorePerdant);						
							$user->addRencontre($rencontre);
							$userManager = $this->get('fos_user.user_manager');
			           		$userManager->updateUser($user);
						}
					}

					elseif ($test == 1) { //idem sauf que cette fois c'est le cas adversaire a gagné
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

					$response = $this->forward('BloomMatchUpBundle:Default:afficherPoule');

					return $response;
				}
		  	}

			return $this->render('BloomMatchUpBundle:Default:entrerResultat.html.twig', array(
		    	'form' => $form->createView(),
		    	));
	}

	public function genererPouleAction()
	{
/* Objectif : construire les nouvelles poules en prenant en compte les résultats des précédentes et les volontés des joueurs
de participer ou non à la suivante.
*/

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
			$listeActuelsParticipants[$i] -> setNouvellePoule( $listeActuelsParticipants[$i] -> getPoule() );
		}
		//je réalise une boucle car je ne sais pas a priori combien j'avais de poules si j'ai changé le paramètre
		//nombreJoueursParPoule
		$i = 1;

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementParPoule = $repository->findByVicInPoule($i);

		$userManager = $this->get('fos_user.user_manager');

		while ( count($classementParPoule) >0 ) {

			for ($j = 1; $j <= 2; $j++) {

				$joueurAClasser = $classementParPoule[count($classementParPoule) - $j];
				$joueurAClasser -> setNouvellepoule($i + 1);

				$userManager->updateUser($joueurAClasser);

				$joueurAClasser = $classementParPoule[ $j - 1 ];
				$joueurAClasser -> setNouvellePoule($i - 1);

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
		$nouveauClassementJoueurs = $repository->findByPouleAndVicAndSetsDESC();

		//on détermine le nombre de poules
		$nombreJoueursParPoule = $this->container->getParameter('nombreJoueursParPoule');

		$nombreJoueurs = count($listeFutursParticipants);
		$nombrePoules = floor($nombreJoueurs/$nombreJoueursParPoule);
		$nombreGrandesPoules = $nombreJoueurs % $nombrePoules;

		//je réinitialise la colonne poule
		$listeUsers = $repository->findAll();

		for ($i=0; $i < count($listeUsers); $i++) { 
			$listeUsers[$i] -> setPoule(NULL);
			$userManager->updateUser($listeUsers[$i]);
		}

		//je ventile dans les poules
		if ($nombreGrandesPoules > 0) {
			for ($i=1; $i <= $nombreGrandesPoules ; $i++) {
				for ($j=0; $j < $nombreJoueursParPoule+1 ; $j++) {

					$k = $j + ($i-1)*($nombreJoueursParPoule+1);

					$nouveauClassementJoueurs[$k] -> setPoule($i);
					$nouveauClassementJoueurs[$k] -> setVictoires(0);
					$nouveauClassementJoueurs[$k] -> setSets(0);					

					$userManager->updateUser($nouveauClassementJoueurs[$k]);
				}		
			}
		}

		for ($i=1; $i <= $nombrePoules - $nombreGrandesPoules ; $i++) {
			for ($j=0; $j < $nombreJoueursParPoule ; $j++) {

				$k = $j + $nombreGrandesPoules*($nombreJoueursParPoule + 1) + ($i-1)*($nombreJoueursParPoule);

				$nouveauClassementJoueurs[$k] -> setPoule($i + $nombreGrandesPoules);
				$nouveauClassementJoueurs[$k] -> setVictoires(0);
				$nouveauClassementJoueurs[$k] -> setSets(0);				

				$userManager->updateUser($nouveauClassementJoueurs[$k]);
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
}