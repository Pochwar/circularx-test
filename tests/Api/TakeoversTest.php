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

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
