<?php

namespace App\Tests\Controller\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use App\Entity\Product;
use App\Entity\Takeover;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;


class TakeoversTest extends ApiTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreate(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        /** @var User $user1 */
        $user1 = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@test-circularx.com']);

        /** @var Product $galaxyS22 */
        $galaxyS22 = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'Galaxy S22']);

        /** @var Product $onePLusNord2T */
        $onePLusNord2T = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'OnePlus Nord 2T']);

        $response = static::createClient()->request('POST', '/api/takeovers', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'owner' => \sprintf('/api/users/%d', $user1->getId()),
                'products' => [
                    [
                        'product' => \sprintf('/api/products/%d', $galaxyS22->getId()),
                        'price' => $galaxyS22->getPrice(),
                    ],
                    [
                        'product' => \sprintf('/api/products/%d', $onePLusNord2T->getId()),
                        'price' => $onePLusNord2T->getPrice(),
                    ],
                ]
            ]
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($content['products'][0]['product']['name'], $galaxyS22->getName());
        $this->assertCount(2, $content['products']);
    }

    public function testCreateNegativePrice(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        /** @var User $user1 */
        $user1 = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@test-circularx.com']);

        /** @var Product $galaxyS22 */
        $galaxyS22 = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => 'Galaxy S22']);

        $response = static::createClient()->request('POST', '/api/takeovers', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'owner' => \sprintf('/api/users/%d', $user1->getId()),
                'products' => [
                    [
                        'product' => \sprintf('/api/products/%d', $galaxyS22->getId()),
                        'price' => -999,
                    ],
                ]
            ]
        ]);

        try {
            $content = json_decode($response->getContent(), true);
        } catch (ServerException $e) {
            $this->assertStringContainsString('price: This value must be positive.', $e->getMessage());
        }
    }

    public function testGetAllTakeovers(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('GET', '/api/takeovers', [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $content);
        $this->assertMatchesResourceCollectionJsonSchema(Takeover::class);

        // First takeover
        $this->assertCount(3, $content[0]['products']);
        $this->assertEquals("user1@test-circularx.com", $content[0]['owner']['email']);

        $this->assertEquals("Galaxy S22", $content[0]['products'][0]['product']['name']);
        $this->assertEquals(119999, $content[0]['products'][0]['price']);

        $this->assertEquals("Galaxy Z Fold 2", $content[0]['products'][1]['product']['name']);
        $this->assertEquals(89999, $content[0]['products'][1]['price']);

        $this->assertEquals("OnePlus Nord 2T", $content[0]['products'][2]['product']['name']);
        $this->assertEquals(42900, $content[0]['products'][2]['price']);

        $totalPrice = $content[0]['products'][0]['price']
            + $content[0]['products'][1]['price']
            + $content[0]['products'][2]['price'];
        $this->assertEquals($totalPrice, $content[0]['totalPrice']);

        // Second takeover
        $this->assertCount(2, $content[1]['products']);
        $this->assertEquals("user2@test-circularx.com", $content[1]['owner']['email']);

        $this->assertEquals("OnePlus 10 Pro", $content[1]['products'][0]['product']['name']);
        $this->assertEquals(91900, $content[1]['products'][0]['price']);

        $this->assertEquals("Galaxy Z Fold 2", $content[1]['products'][1]['product']['name']);
        $this->assertEquals(89999, $content[1]['products'][1]['price']);

        $totalPrice2 = $content[1]['products'][0]['price']
            + $content[1]['products'][1]['price'];
        $this->assertEquals($totalPrice2, $content[1]['totalPrice']);
    }

    public function testGetFirstTakeover(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('GET', '/api/takeovers/1', [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();

        // First takeover
        $this->assertCount(3, $content['products']);
        $this->assertEquals("user1@test-circularx.com", $content['owner']['email']);

        $this->assertEquals("Galaxy S22", $content['products'][0]['product']['name']);
        $this->assertEquals(119999, $content['products'][0]['price']);

        $this->assertEquals("Galaxy Z Fold 2", $content['products'][1]['product']['name']);
        $this->assertEquals(89999, $content['products'][1]['price']);

        $this->assertEquals("OnePlus Nord 2T", $content['products'][2]['product']['name']);
        $this->assertEquals(42900, $content['products'][2]['price']);

        $totalPrice = $content['products'][0]['price']
            + $content['products'][1]['price']
            + $content['products'][2]['price'];
        $this->assertEquals($totalPrice, $content['totalPrice']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
