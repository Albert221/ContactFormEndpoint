<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpRecord;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class IpRecordsRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpRecord::class);
    }

    /**
     * @param string $ip
     * @param DateInterval $interval
     * @param DateTime|null $now Defaults to now.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function findForIpWithinInterval(string $ip, DateInterval $interval, DateTime $now = null): array
    {
        if ($now == null) {
            $now = new DateTime();
        }

        $backInTime = $now->sub($interval);

        return $this
            ->createQueryBuilder('i')
            ->where('i.ip = :ip')
            ->andWhere('i.createdAt > :backInTime')
            ->setParameter(':ip', $ip)
            ->setParameter(':backInTime', $backInTime)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param IpRecord $ipRecord
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persist(IpRecord $ipRecord): void
    {
        $this->getEntityManager()->persist($ipRecord);
        $this->getEntityManager()->flush($ipRecord);
    }
}
