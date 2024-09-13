<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusTpayPlugin\E2E;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Webmozart\Assert\Assert;

abstract class E2ETestCase extends PantherTestCase
{
    protected Client $client;

    protected LoaderInterface|null $fixtureLoader = null;

    protected EntityManagerInterface|null $entityManager = null;

    protected function setUp(): void
    {
        $this->client = static::createPantherClient();

        parent::setUp();
    }

    /**
     * @before
     */
    public function setUpDatabase(): void
    {
        $container = static::getContainer();
        Assert::notNull($container);

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        Assert::notNull($entityManager);

        $this->entityManager = $entityManager;
        $this->entityManager->getConnection()->connect();

        /** @var LoaderInterface $fixtureLoader */
        $fixtureLoader = $container->get('fidry_alice_data_fixtures.loader.doctrine');
        $this->fixtureLoader = $fixtureLoader;

        $this->purgeDatabase();
    }

    protected function purgeDatabase(): void
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $this->entityManager->clear();
    }

    protected function loadFixtures(array $fixtureFiles): void
    {
        $base = dirname(__FILE__, 1) . '/Resources/fixtures';
        $fixturePaths = array_map(fn ($file) => $base . '/' . $file, $fixtureFiles);

        $this->fixtureLoader->load($fixturePaths);
    }
}
