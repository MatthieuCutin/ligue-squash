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

	public function FindByPouleAndVicAndSetsDESC()
	{
		$qb = $this->_em->createQueryBuilder();
		$qb->select('u')
		->from('BloomUserBundle:User', 'u')	
		->orderBy('u.nouvellepoule', 'ASC')
		->addOrderBy('u.victoires', 'ASC')
		->addOrderBy('u.sets', 'ASC');

		return $qb->getQuery()
				  ->getResult();
	}	
}