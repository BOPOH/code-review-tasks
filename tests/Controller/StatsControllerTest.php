<?php

namespace App\Tests\Controller;

use App\Entity\Link as LinkEntity;
use App\Entity\LinkVisit as LinkVisitEntity;
use App\Link\Command\Add;
use App\Link\DTO\Link as LinkDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group new
 */
class StatsControllerTest extends WebTestCase
{
    private ?Add $addCommand;
    private ?EntityManagerInterface $em;
    private ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->addCommand = static::getContainer()->get(Add::class);
        $this->em = static::getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        $this->client = null;
        $this->addCommand = null;
        $this->em = null;
    }

    public function testLinkVisitingIncreasesVisitCounter()
    {
        $em = $this->em;

        $dto = new LinkDTO();
        $dto->setUrl('https://google.com');
        $dto->setTitle('Title');

        $link = $this->addCommand->add($dto);

        $this->client->request(Request::METHOD_GET, '/links/visit/' . $link->getShortUrl());

        $em->clear();
        /** @var ?LinkEntity $link */
        $link = $em->find(LinkEntity::class, $link->getId());
        $visitCount = 0;
        if ($link) {
            $visitCount = $link->getLinkVisits()->count();
            $em->remove($link);
            $em->flush();
        }

        $this->assertEquals(1, $visitCount);
    }

    public function testTotalStatsShowsCorrectData()
    {
        $em = $this->em;

        $dto = new LinkDTO();
        $dto->setUrl('https://google.com');
        $dto->setTitle('Title');

        $firstLink = $this->addCommand->add($dto);
        $firstLinkId = $firstLink->getId();

        $dto = new LinkDTO();
        $dto->setUrl('https://yandex.ru');
        $dto->setTitle('Title');

        $secondLink = $this->addCommand->add($dto);
        $secondLinkId = $secondLink->getId();

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 1');
        $linkVisit->setDate(new \DateTime('2020-01-01'));
        $secondLink->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 2');
        $linkVisit->setDate(new \DateTime('2020-01-01'));
        $secondLink->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 1');
        $linkVisit->setDate(new \DateTime('2020-01-01'));
        $firstLink->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $em->flush();

        $this->client->request(Request::METHOD_GET, '/stats');

        /** @var ?LinkEntity $firstLink */
        $firstLink = $em->find(LinkEntity::class, $firstLinkId);
        if ($firstLink) {
            $em->remove($firstLink);
            $em->flush();
        }

        /** @var ?LinkEntity $secondLink */
        $secondLink = $em->find(LinkEntity::class, $secondLinkId);
        if ($secondLink) {
            $em->remove($secondLink);
            $em->flush();
        }

        $response = $this->client->getResponse();
        $data = \json_decode($response->getContent(), true);

        $this->assertCount(2, $data);
        $this->assertEquals([
            [
                'id'           => $secondLinkId,
                'unique_views' => 2,
                'total_views'  => 2,
            ],
            [
                'id'           => $firstLinkId,
                'unique_views' => 1,
                'total_views'  => 1,
            ],
        ], $data);
    }

    public function testLinkStatsShowsCorrectData()
    {
        $em = $this->em;

        $dto = new LinkDTO();
        $dto->setUrl('https://google.com');
        $dto->setTitle('Title');

        $link = $this->addCommand->add($dto);
        $linkId = $link->getId();

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 1');
        $linkVisit->setDate(new \DateTime('2020-01-01'));
        $link->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 2');
        $linkVisit->setDate(new \DateTime('2020-01-01'));
        $link->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setIp('IP1');
        $linkVisit->setUserAgent('UserAgent 1');
        $linkVisit->setDate(new \DateTime('2020-01-02'));
        $link->addLinkVisit($linkVisit);
        $em->persist($linkVisit);

        $em->flush();

        $this->client->request(Request::METHOD_GET, '/stats/' . $linkId);

        /** @var ?LinkEntity $link */
        $link = $em->find(LinkEntity::class, $linkId);
        if ($link) {
            $em->remove($link);
            $em->flush();
        }

        $response = $this->client->getResponse();
        $data = \json_decode($response->getContent(), true);

        $this->assertCount(2, $data);
        $this->assertEquals([
            [
                'unique_views' => 1,
                'total_views'  => 1,
                'visit_date'   => '2020-01-02',
            ],
            [
                'unique_views' => 2,
                'total_views'  => 2,
                'visit_date'   => '2020-01-01',
            ],
        ], $data);
    }
}
