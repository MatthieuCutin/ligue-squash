<?php

namespace Bloom\MatchUpBundle\Entity;

use Doctrine\ORM\EntityRepository;


class RencontreRepository extends EntityRepository
{

	public function loadRencontreByIdVainqueur($IdVainqueur)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.IdVainqueur = :IdVainqueur')
            ->setParameter('IdVainqueur', $IdVainqueur);


        return $qb->getQuery()
                  ->getResult();
    }

	public function loadRencontreById($Id)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.Id = :Id')
            ->setParameter('Id', $Id);


        return $qb->getQuery()
                  ->getOneOrNullResult();
    }

    public function findOneByRencontreByIdVainqueurAndIdPerdant($IdVainqueur, $IdPerdant)
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->where('r.idVainqueur = :idVainqueur')
            ->andWhere('r.IdPerdant = :IdPerdant')
            ->setParameter('idVainqueur', $IdVainqueur)
            ->setParameter('IdPerdant', $IdPerdant);

        return $qb->getQuery()
                  ->getOneOrNullResult();
    }    
}
