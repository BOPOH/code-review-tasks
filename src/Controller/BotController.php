<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\LinkVisit;
use App\Link\Command\Add;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bot")
 */
class BotController extends AbstractController
{
    /**
     * @Route("")
     */
    public function indexAction(EntityManagerInterface $em, Add $addCommand)
    {
        $token = $this->container->get('TELEGRAM_TOKEN');
        $config = [
            "telegram" => [
                "token" => $token
            ]
        ];

        \BotMan\BotMan\Drivers\DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        $botman = \BotMan\BotMan\BotManFactory::create($config);

        $controller = $this;
        $botman->hears('long_url {long_url} {title}', function(\BotMan\BotMan\BotMan $bot, string $shortUrl, string $title) use ($controller, $em, $addCommand) {
            $controller->createRequest($bot, $em, $addCommand, $shortUrl, $title);
        });

        $botman->hears('short_url {short_url}', function(\BotMan\BotMan\BotMan $bot, string $shortUrl) use ($controller, $em) {
            $controller->shortUrlRequest($bot, $em, $shortUrl);
        });

        $botman->hears('title {regexp}', function(\BotMan\BotMan\BotMan $bot, string $titleRegexp) use ($controller, $em) {
            $controller->titleRequest($bot, $em, $titleRegexp);
        });

        $botman->hears('delete {short_url}', function(\BotMan\BotMan\BotMan $bot, string $shortUrl) use ($controller, $em) {
            $controller->deleteRequest($bot, $em, $shortUrl);
        });

        $botman->hears('stats {short_url}', function(\BotMan\BotMan\BotMan $bot, string $shortUrl) use ($controller, $em) {
            $controller->urlStatRequest($bot, $em, $shortUrl);
        });

        $botman->hears('stats', function(\BotMan\BotMan\BotMan $bot) use ($controller, $em) {
            $controller->totalStatRequest($bot, $em);
        });

        $botman->listen();
    }

    public function shortUrlRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em, string $shortUrl)
    {
        /** @var ?Link $link */
        $link = $em->getRepository(Link::class)->findOneBy([
            'shortUrl' => $shortUrl,
        ]);
        if (!$link) {
            $bot->reply('Link not found');
            return;
        }

        $statResult = $em->getRepository(LinkVisit::class)
            ->createQueryBuilder('link_visit')
            ->select('COUNT(1) AS total_views')
            ->addSelect('COUNT(DISTINCT CONCAT(link_visit.ip, link_visit.userAgent)) AS unique_views')
            ->andWhere('link_visit.link = :link')
            ->setParameter('link', $link)
            ->getQuery()
            ->getArrayResult();
        if (!$statResult) {
            $statResult = [
                'total_views'  => 0,
                'unique_views' => 0,
            ];
        }

        $bot->reply(sprintf(
            "%s\n%s\n%s\n%s\n%s",
            $link->getTitle(),
            $link->getUrl(),
            $link->getShortUrl(),
            $statResult['total_views'],
            $statResult['unique_views'],
        ));
    }

    public function titleRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em, string $titleRegexp)
    {
        /** @var Link[] $links */
        $links = $em->getRepository(Link::class)->findBy([
            'title' => $titleRegexp,
        ]);
        foreach ($links as $link) {
            $bot->reply(sprintf(
                "%s\n%s\n%s",
                $link->getTitle(),
                $link->getUrl(),
                $link->getShortUrl()
            ));
        }
    }

    public function createRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em, Add $addCommand, string $longUrl, string $title)
    {
        $dto = new \App\Link\DTO\Link();
        $dto->setUrl($longUrl);
        $dto->setTitle($title);
        $link = $addCommand->add($dto);

        $bot->reply($link->getShortUrl());
    }

    public function deleteRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em, string $shortUrl)
    {
        $link = $em->getRepository(Link::class)->findOneBy([
            'shortUrl' => $shortUrl,
        ]);
        if ($link) {
            $em->remove($link);
            $em->flush();
        }

        $bot->reply('Done');
    }

    public function urlStatRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em, string $shortUrl)
    {
        $link = $em->getRepository(Link::class)->findOneBy([
            'shortUrl' => $shortUrl,
        ]);
        if (!$link) {
            $bot->reply('Link not found');
            return;
        }

        $result = $em->getRepository(LinkVisit::class)
            ->createQueryBuilder('link_visit')
            ->select('COUNT(1) AS total_views')
            ->addSelect('COUNT(DISTINCT CONCAT(link_visit.ip, link_visit.userAgent)) AS unique_views')
            ->addSelect('DATE(link_visit.date) AS visit_date')
            ->addGroupBy('visit_date')
            ->addOrderBy('visit_date', 'DESC')
            ->andWhere('link_visit.link = :link')
            ->setParameter('link', $link)
            ->getQuery()
            ->getArrayResult();
        if (!$result) {
            $bot->reply('Stat is empty');
            return;
        }

        $bot->reply(sprintf('[%s %s %s]', $result['visit_date'], $result['total_views'], $result['unique_views']));
    }

    public function totalStatRequest(\BotMan\BotMan\BotMan $bot, EntityManagerInterface $em)
    {
        $result = $em->getRepository(LinkVisit::class)
            ->createQueryBuilder('link_visit')
            ->join('link_visit.link', 'link')
            ->select('COUNT(1) AS total_views')
            ->addSelect('COUNT(DISTINCT CONCAT(link_visit.ip, link_visit.userAgent)) AS unique_views')
            ->addSelect('link.url')
            ->addSelect('link.title')
            ->addGroupBy('link')
            ->addOrderBy('unique_views', 'DESC')
            ->getQuery()
            ->getArrayResult();
        if (!$result) {
            $bot->reply('Stat is empty');
            return;
        }

        $bot->reply(sprintf('[%s %s %s %s]', $result['url'], $result['total_views'], $result['unique_views'], $result['title']));
    }
}
