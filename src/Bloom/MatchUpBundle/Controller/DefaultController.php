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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends Controller
{

	public function entrerResultatAction(Request $request)
	{
/* Objectif : entrer une rencontre en base de donnée avec les informations sur le vainqueur, le perdant et le score du perdant
Cette action modifie également une rencontre déjà présente en base de donnée.
*/
		//Création du formulaire
		$rencontre = new Rencontre();
        $user = $this->container->get('security.context')->getToken()->getUser();

		$session = new Session();
		$session -> set('profil', $user);

        $form = $this->createForm('bloom_adversaire_poule_score', $rencontre);

	    $request = $this->get('request');

	    if ($user->getPoule() >0) {

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

		else {
			return $this->render('BloomMatchUpBundle:Default:entrerResultat.html.twig');
		}
	
	}

	public function afficherPouleAction( $numeroPoule = 0 )
	{
/* Objectif : Récupérer les infos sur les joueurs et les rencontres pour les passer à la vue
*/

		//Par défaut on ne précise pas quelle poule on souhaite afficher (numeroPoule=0), si user est dans une poule on affiche
		//sa poule par defaut, sinon on affiche la poule 1.

		//La vue classementPoule peut demander à afficher une autre poule.
		if ($numeroPoule == 0) {
			$user = $this->container->get('security.context')->getToken()->getUser();

			if ($numeroPoule = $user -> getPoule() > 0) {
				$numeroPoule = $user -> getPoule();
			}
			else{
				$numeroPoule = 1;
			}
		}

		//Je construit le système de poules
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listeJoueurs = $repository->findAll();

		$nombreJoueursParPoule = $this->container->getParameter('nombreJoueursParPoule');

		$nombreJoueurs = count($listeJoueurs);
		$nombrePoules = floor($nombreJoueurs/$nombreJoueursParPoule); 
		if ($nombrePoules ==0) {return $this->render('BloomMatchUpBundle:Default:classementPoule.html.twig');} //quand on a pas encore de poules
		$nombreGrandesPoules = $nombreJoueurs % $nombrePoules;

		//Je récupère les Id des joueurs de la poule
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementPoule = $repository->findByVicInPoule($numeroPoule);

		for ($i=0; $i < count($classementPoule) ; $i++) {
			$joueursId[$i] = $classementPoule[$i] -> getId();
		}

		//Je récupère toutes les rencontres
		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomMatchUpBundle:Rencontre');
		$listeRencontres = $repository->findAll();

		$rencontresIdVainqueur[0] = 0;
		$rencontresIdPerdant[0] =0;
		$scorePerdant[0] =0;

		for ($i=0; $i < count($listeRencontres) ; $i++) {
			$rencontresIdVainqueur[$i] = $listeRencontres[$i] -> getIdVainqueur();
			$rencontresIdPerdant[$i] = $listeRencontres[$i] -> getIdPerdant();
			$scorePerdant[$i] = $listeRencontres[$i] -> getScorePerdant();
		}

	    return $this->render('BloomMatchUpBundle:Default:classementPoule.html.twig', array(
	    	'classementPoule' => $classementPoule,
	    	'numeroPoule'     => $numeroPoule,
	    	'nombrePoules'    => $nombrePoules,
	    	'rencontresIdVainqueur' => $rencontresIdVainqueur,
	    	'rencontresIdPerdant'   => $rencontresIdPerdant,
	    	'scorePerdant'          => $scorePerdant, 
	    	'joueursId'       => $joueursId  
	    	));

	}

	public function homepageAction()
	{

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listeJoueurs = $repository->findByPouleAndVicAndSets();

		$user = $this->container->get('security.context')->getToken()->getUser();

		$em = $this->getDoctrine()->getManager();
		$em->flush();


		return $this->render('BloomMatchUpBundle:Default:homepage.html.twig', array(
			    	'listeJoueurs'    => $listeJoueurs
			    	));
	}

	public function matchupAction()
	{
		$user = $this->container->get('security.context')->getToken()->getUser();

		//Je récupère les joueurs de la poule
		if ($user->getPoule() !== NULL) {
			$repository = $this->getDoctrine()
			->getManager()
			->getRepository('BloomUserBundle:User');
			$joueurs = $repository->findByPouleAndName($user -> getPoule());

			//J'enlève user
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

	public function profilAction($idAdversaire)
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