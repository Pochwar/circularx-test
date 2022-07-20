<?php

namespace App\Tests\Controller\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Takeover;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;


class ProductsTest extends ApiTestCase
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

    public function testGetAllProducts(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('GET', '/api/products', [
            'headers' => ['Accept' => 'application/json'],
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertCount(6, $content);
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);

        $this->assertEquals($content[0]['name'], "Galaxy S22");
        $this->assertEquals($content[1]['name'], "Galaxy Z Fold 2");
        $this->assertEquals($content[2]['name'], "OnePlus 10 Pro");
        $this->assertEquals($content[3]['name'], "OnePlus Nord 2T");
        $this->assertEquals($content[4]['name'], "Moto G41");
        $this->assertEquals($content[5]['name'], "Moto G50");
    }

    public function testCreate(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        /** @var Brand $brand */
        $brand = $this->entityManager->getRepository(Brand::class)->findOneBy(['name' => 'Samsung']);

        $response = static::createClient()->request('POST', '/api/products', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'name' => 'Test Phone',
                'brand' => \sprintf('api/brands/%s', $brand->getId()),
                'price' => 45600
            ]
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();

        $this->assertEquals('Test Phone', $content['name']);
        $this->assertEquals('Samsung', $content['brand']['name']);
        $this->assertEquals('45600', $content['original_price']);
    }

    public function testCreateAlreadyExists(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        /** @var Brand $brand */
        $brand = $this->entityManager->getRepository(Brand::class)->findOneBy(['name' => 'Samsung']);

        $response = static::createClient()->request('POST', '/api/products', [
            'headers' => ['Accept' => 'application/json'],
            'json' => [
                'name' => 'OnePlus 10 Pro', // Already existing name for this brand
                'brand' => \sprintf('api/brands/%s', $brand->getId()),
                'price' => 45600
            ]
        ]);

        try {
            $content = json_decode($response->getContent(), true);
        } catch (ServerException $e) {
            $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
        }
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
