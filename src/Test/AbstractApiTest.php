<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use App\Api\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Api\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractApiTest extends WebTestCase
{
    use ApiTestAssertionsTrait;
    use AuthenticationTrait;

    protected ?KernelBrowser $client = null;
    protected Router $router;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        // Init client
        $this->getClient();
        $this->router = new Router($this->loadComponent(RouterInterface::class));
        $this->em = $this->loadComponent('doctrine')->getManager();
    }

    /**
     * @param string $componentID
     *
     * @return object|null|RouterInterface
     */
    protected function loadComponent(string $componentID)
    {
        return static::getContainer()->get($componentID);
    }

    protected function send(string|array $route, array $params = [], array $headers = []): Crawler
    {
        $client = $this->getClient();
        [$method, $uri] = $this->router->createRoute($route);

        $client->setServerParameter('CONTENT_TYPE', $this->requestContentType);
        $client->setServerParameter('HTTP_ACCEPT', $this->requestAssept);
        if ($this->getToken() !== null) {
            $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $this->getToken());
        }

        return $client->request($method, $uri, [], [], $headers, json_encode($params));
    }

    protected function sendLd(string|array $route, array $params = [], array $headers = []): Crawler
    {
        [$oldContentType, $oldAccept] = [$this->requestContentType, $this->requestAssept];
        [$this->requestContentType, $this->requestAssept] = [Request::CONTENT_TYPE_JSON_LD, Request::CONTENT_TYPE_JSON_LD];

        try {
            return $this->send($route, $params, $headers);
        } finally {
            [$this->requestContentType, $this->requestAssept] = [$oldContentType, $oldAccept];
        }
    }

    protected function getClient(): KernelBrowser
    {
        if ($this->client === null) {
            $this->client = self::createClient();
        }

        return $this->client;
    }
}