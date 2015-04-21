<?php

namespace Bloom\MatchUpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Bloom\UserBundle\Entity\User;
use Bloom\MatchUpBundle\Entity\Rencontre;
use Bloom\MatchUpBundle\Form\Type\AdversairePouleFormType;
use Bloom\MatchUpBundle\Form\Type\AdversairePouleScoreFormType;
use Symfony\Component\DependencyInjection\ContainerAware;

class DefaultController extends Controller
{

	public function EntrerResultatAction(Request $request)
	{
		$user = $this->container->get('security.context')->getToken()->getUser();

		$pouleuser = $user-> getpoule();

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementpoule = $repository->findByPoule($pouleuser);

        $securityContext = $this->container->get('security.context');
        $form = $this->createForm(
            new AdversairePouleFormType($securityContext)
        );

	    $request = $this->get('request');

	    if ($request->getMethod() == 'POST') {

	      $form->bind($request);

	      if ($form->isValid()) {

	      	$session = new Session();

			$adversaire = $form -> getData();

				$adversaire = (string) $adversaire;

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');
				$adversaire = $repository->LoadUserByUsername($adversaire);

			if ($adversaire !== NULL) {

				$adversaireId = $adversaire -> getId();
				$session->set('adversaireId', $adversaireId);
			}

	        $form2 = $this->createForm(
           		new AdversairePouleScoreFormType()
        	);

			$form2->handleRequest($request);

		    if ($form2->isValid()) {

				$user = $this->container->get('security.context')->getToken()->getUser();

				$rencontre = new Rencontre;
				$em = $this->getDoctrine()
					->getManager();

				$rencontre = $form2 -> getData();

				$adversaireId = $session->get('adversaireId');

				if ($test = $rencontre -> getIdVainqueur() == 0) {
					$rencontre -> setIdVainqueur($user -> getId());
				}
				elseif ($test = $rencontre -> getIdVainqueur() == 1) {
					$rencontre -> setIdVainqueur($adversaireId);
				}

    			$em->persist($rencontre);
				$em->flush();

				$repository = $this->getDoctrine()
				->getManager()
				->getRepository('BloomUserBundle:User');
				$adversaire = $repository->LoadUserById($adversaireId);

				$user->addRencontre($rencontre);
				$adversaire->addRencontre($rencontre);
	            $userManager = $this->get('fos_user.user_manager');
	            $userManager->updateUser($user);
	            $userManager->updateUser($adversaire);


				return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
					'adversaire' => $adversaireId,
					));
			}

			return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
				'form2' => $form2->createView(),
				));

    		/*return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
			    'adversaire'  => $adversaire,
    		));*/
	      }
	  	}

		return $this->render('BloomMatchUpBundle:Default:entrerresultat.html.twig', array(
	    	'form' => $form->createView(),
	    	));
	
	}

	public function ClassementAction()
	{

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classement = $repository->findByPoints();

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementniv = $repository->findByNiv();

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementpoule = $repository->findByPoule(1);





		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listejoueurs = $repository->findAll();

		$NombreJoueursParPoule = 4;

		$NombreJoueurs = count($listejoueurs);
		$NombrePoules = floor($NombreJoueurs/$NombreJoueursParPoule);
		$NombreGrandesPoules = $NombreJoueurs % $NombrePoules;

		for ($i=0; $i <= $NombrePoules ; $i++) { 
			$repository = $this->getDoctrine()
			->getManager()
			->getRepository('BloomUserBundle:User');
			$classementpouletot[$i] = $repository->findByVicInPoule($i);
		}


		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classementpoule = $repository->findByVicInPoule(1);

		$joueurAClasser = $classementpoule[count($classementpoule)-1];
		$vic = $joueurAClasser -> getVictoires();
		$joueurAClasser -> setVictoires($vic + 0);

		$userManager = $this->get('fos_user.user_manager');

		$userManager->updateUser($joueurAClasser);


	    return $this->render('BloomMatchUpBundle:Default:classement.html.twig', array(
	    	'classement'          => $classement,
	    	'classementniv'       => $classementniv,
	    	'classementpoule'     => $classementpoule,
	    	'listejoueurs'        => $listejoueurs,
	    	'NombrePoules'        => $NombrePoules,
	    	'NombreJoueurs'       => $NombreJoueurs,
	    	'NombreGrandesPoules' => $NombreGrandesPoules,
	    	'classementpouletot'  => $classementpouletot
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
				$joueurAClasser -> setNouvellepoule($i + 1);

				$userManager = $this->get('fos_user.user_manager');
				$userManager->updateUser($joueurAClasser);
			}
		}

		for ($i = 1; $i <= $NombrePoules; $i++) {

			$repository = $this->getDoctrine()
			->getManager()
			->getRepository('BloomUserBundle:User');
			$listejoueurs = $repository->findByPouleAndName($i);

			foreach ($listejoueurs as $value) {

				$rencontre = new Rencontre;
				$value->addRencontre($rencontre);

			    $em = $this->getDoctrine()
	          	 ->getManager();
	     	    $em->flush();

				$userManager = $this->get('fos_user.user_manager');
				$userManager->updateUser($value);	     	    
			}
		}


	    return $this->render('BloomMatchUpBundle:Default:homepage.html.twig', array(
	    	));

	}

	public function HomepageAction()
	{

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$listejoueurs = $repository->findAll();

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

		//Je récupère le classement des joueurs

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classement = $repository->findByPoints();

		$position = 0;

		while ($classement[$position] != $user AND $position<10)
		{
			$position++;
		}

		$adversaire1 = $classement[$position-2];
		$adversaire2 = $classement[$position-1];
		$adversaire3 = $classement[$position+1];

	    return $this->render('BloomMatchUpBundle:Default:matchup.html.twig', array(
	    	'user'        => $user,
	    	'position'    => $position,
	    	'adversaire1' => $adversaire1,
	    	'adversaire2' => $adversaire2,
	    	'adversaire3' => $adversaire3
	    	));
	}

	public function ProfilAction()
	{
		$user = $this->container->get('security.context')->getToken()->getUser();

		//Je récupère le classement des joueurs

		$repository = $this->getDoctrine()
		->getManager()
		->getRepository('BloomUserBundle:User');
		$classement = $repository->findByPoints();

		$position = 0;

		while ($classement[$position] != $user AND $position<10)
		{
			$position++;
		}

		$adversaire1 = $classement[$position-2];
		$adversaire2 = $classement[$position-1];
		$adversaire3 = $classement[$position+1];


		If (isset($_POST['profil1'])) {

			return $this->render('BloomMatchUpBundle:Default:profil.html.twig', array(
	    	'user'        => $user,
	    	'adversaire' => $adversaire1,
	    	));
		}

		If (isset($_POST['profil2'])) {
			return $this->render('BloomMatchUpBundle:Default:profil.html.twig', array(
	    	'user'        => $user,
	    	'adversaire' => $adversaire2,
	    	));
		}

		If (isset($_POST['profil3'])) {
			return $this->render('BloomMatchUpBundle:Default:profil.html.twig', array(
	    	'user'        => $user,
	    	'adversaire' => $adversaire3,
	    	));
		}
		return $this->render('BloomMatchUpBundle:Default:homepage.html.twig');
	}
}