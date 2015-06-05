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

	public function SupprimerProfilAction()
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

				$em = $this->getDoctrine()->getEntityManager();
				$em->remove($profil);
				$em->flush();

				return $this->render('BloomMatchUpBundle:Admin:supprimerProfil.html.twig');
			}
		}

		return $this->render('BloomMatchUpBundle:Admin:supprimerProfil.html.twig', array(
			'form' => $form->createView(),
			));

	}

	public function ModifierProfilAction()
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}

		$profil = new User;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->createForm('bloom_modifier_profil', $profil);

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      	$form->bind($request);

			if ($form->isValid()) {

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');

				$em = $this->getDoctrine()->getEntityManager();

				$profilUsername = $form->get('User')->getData();
				$nouvellesVictoires = $form->get('victoires')->getData();
				$nouvellePoule = $form->get('poule')->getData();
				$nouveauxSets = $form->get('sets')->getData();

				$profilUsername = (string) $profilUsername;

				$profil = $repository->LoadUserByUsername($profilUsername);

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

	public function SelectionnerProfilAction()
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

		public function ModifierResultatAction($idProfil, Request $request)
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
        	throw new AccessDeniedException();
    	}
		
		//attention ici $user ne désigne pas l'utilisateur mais le profil selectionné juste avant
		//c'est pour pouvoir réutiliser le code que je n'ai pas changé $user
        $repositoryUser = $this->getDoctrine()
					->getManager()
					->getRepository('BloomUserBundle:User');
		$user = $repositoryUser->LoadUserById($idProfil);

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
					$adversaireUsername = $form->get('User')->getData();

					$adversaireUsername = (string) $adversaireUsername;

					//je récupère l'objet adversaire au complet
					$repositoryUser = $this->getDoctrine()
					->getManager()
					->getRepository('BloomUserBundle:User');
					$adversaire = $repositoryUser->LoadUserByUsername($adversaireUsername);

					$repositoryRencontre = $this->getDoctrine()
					->getManager()
					->getRepository('BloomMatchUpBundle:Rencontre');


					$em = $this->getDoctrine()->getEntityManager();

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

					$response = $this->forward('BloomMatchUpBundle:Default:AfficherPoule');

					return $response;
				}
		  	}

			return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
		    	'form' => $form->createView(),
		    	));
	}
}