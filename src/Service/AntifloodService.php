<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\IpRecord;
use App\Repository\IpRecordsRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use RuntimeException;

class AntifloodService
{
    /**
     * @var IpRecordsRepository
     */
    private $ipRecordsRepository;

    /**
     * @var string
     */
    private $antifloodInterval;

    /**
     * @var int
     */
    private $antifloodQuantity;

    /**
     * @param IpRecordsRepository $ipRecordsRepository
     * @param string $antifloodInterval
     * @param int $antifloodQuantity
     */
    public function __construct(
        IpRecordsRepository $ipRecordsRepository,
        string $antifloodInterval,
        int $antifloodQuantity
    ) {
        $this->ipRecordsRepository = $ipRecordsRepository;
        $this->antifloodInterval = $antifloodInterval;
        $this->antifloodQuantity = $antifloodQuantity;
    }

    /**
     * @param string $clientIp
     *
     * @return bool
     */
    public function isIpFlooding(string $clientIp): bool
    {
        try {
            $records = $this->ipRecordsRepository->findForIpWithinInterval(
                $clientIp,
                new DateInterval($this->antifloodInterval)
            );
        } catch (Exception $e) {
            throw new RuntimeException('Checking IP flooding failed', 0, $e);
        }

        return count($records) > $this->antifloodQuantity;
    }

    /**
     * @param string $clientIp
     */
    public function saveIpRecord(string $clientIp): void
    {
        $ipRecord = new IpRecord();
        $ipRecord->setIp($clientIp);
        $ipRecord->setCreatedAt(new DateTime());

        try {
            $this->ipRecordsRepository->persist($ipRecord);
        } catch (ORMException $e) {
            throw new RuntimeException('There\'s something wrong with saving IP record to the database');
        }
    }
}
