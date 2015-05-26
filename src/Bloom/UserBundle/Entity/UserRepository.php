<?php

namespace Bloom\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

	public function loadUserByUsername($username)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username);


        return $qb->getQuery()
                  ->getOneOrNullResult();
    }

    public function findAll()
    {
		$qb = $this->createQueryBuilder('u');

		return $qb->getQuery()
				  ->getResult();
    }

	public function loadUserById($Id)
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $Id);


        return $qb->getQuery()
                  ->getOneOrNullResult();
    }

	public function FindByVicInPoule($poule)
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')
		->where('u.poule = :poule')
		->setParameter('poule', $poule)
		->orderBy('u.victoires', 'DESC')
		->addOrderBy('u.sets', 'DESC');

		return $qb->getQuery()
				  ->getResult();
	}

	public function FindByPouleAndName($poule)
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')
		->where('u.poule = :poule')
		->setParameter('poule', $poule)		
		->orderBy('u.poule', 'ASC')
		->addOrderBy('u.username', 'ASC');

		return $qb->getQuery()
				  ->getResult();
	}	

	public function FindByVicInPouleForm($poule, $username)
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')
		->where('u.poule = :poule')
		->setParameter('poule', $poule)		
		->andWhere('u.username != :username')
		->setParameter('username', $username)
		->orderBy('u.victoires', 'DESC')
		->addOrderBy('u.sets', 'DESC');

		return $qb;
	}

	public function FindByPouleAndVicAndSets()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')	
		->orderBy('u.poule', 'DESC')
		->addOrderBy('u.victoires', 'DESC')
		->addOrderBy('u.sets', 'DESC');

		return $qb->getQuery()
				  ->getResult();
	}	

	public function findByPouleAndVicAndSetsDESC()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')
		->where('u.participation = :participation')
		->setParameter('participation', 1)
		->orderBy('u.nouvellepoule', 'ASC')
		->addOrderBy('u.victoires', 'ASC')
		->addOrderBy('u.sets', 'ASC');

		return $qb->getQuery()
				  ->getResult();
	}

	public function findActuelsParticipants()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')	
		->where('u.poule > :poule')
		->setParameter('poule', 0);

		return $qb->getQuery()
				  ->getResult();
	}

	public function findFutursParticipants()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')	
		->where('u.participation = :participation')
		->setParameter('participation', 1);

		return $qb->getQuery()
				  ->getResult();
	}

		public function findNoFutursParticipants()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')	
		->where('u.participation = :participation')
		->setParameter('participation', 0);

		return $qb->getQuery()
				  ->getResult();
	}
}
