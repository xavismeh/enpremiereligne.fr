<?php

namespace App\Repository;

use App\Entity\Helper;
use App\Entity\HelpRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HelpRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpRequest[]    findAll()
 * @method HelpRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpRequest::class);
    }

    public function clearOldOwnerRequests(string $email)
    {
        foreach ($this->findBy(['email' => strtolower($email)]) as $request) {
            $this->_em->remove($request);
        }

        $this->_em->flush();
    }

    public function clearOwnerRequestsByUuid(string $ownerUuid)
    {
        foreach ($this->findBy(['ownerUuid' => $ownerUuid]) as $request) {
            $this->_em->remove($request);
        }

        $this->_em->flush();
    }

    public function closeRequestsOf(string $ownerUuid, ?Helper $withHelper, string $type)
    {
        $requestQuery = $this->createQueryBuilder('r')
            ->update()
            ->set('r.finished', 'true')
            ->where('r.ownerUuid = :ownerUuid')
            ->setParameter('ownerUuid', $ownerUuid)
            ->andWhere('r.helpType = :type')
            ->setParameter('type', $type)
        ;

        if ($withHelper) {
            $requestQuery->set('r.matchedWith', $withHelper->getId());
        }

        $requestQuery->getQuery()->execute();
    }
}
