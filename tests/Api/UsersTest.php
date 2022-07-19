<?php

namespace App\Tests\Controller\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\HttpClient\Exception\ClientException;


class UsersTest extends ApiTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGetAll(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('GET', '/api/users');

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($content['hydra:member'][0]['email'], "user1@test-circularx.com");
        $this->assertEquals($content['hydra:member'][1]['email'], "user2@test-circularx.com");

        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testCreateOk(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('POST', '/api/users', [
            'json' => [
                'email' => 'test@circularx.com'
            ]
        ]);

        $content = json_decode($response->getContent(), true);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($content['email'], "test@circularx.com");
    }

    public function testCreateEmailDuplicate(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $client = static::createClient();

        $response = $client->request('POST', '/api/users', [
            'json' => [
                'email' => 'user2@test-circularx.com'
            ]
        ]);

        try {
            $content = json_decode($response->getContent(), true);
        } catch (ClientException $e) {
            $this->assertStringContainsString('email: This value is already used.', $e->getMessage());
        }
    }

    public function testCreateEmailNotValid(): void
    {
        $this->databaseTool->loadFixtures([
            AppFixtures::class
        ]);

        $response = static::createClient()->request('POST', '/api/users', [
            'json' => [
                'email' => 'user2@test-circularx'
            ]
        ]);

        try {
            $content = json_decode($response->getContent(), true);
        } catch (ClientException $e) {
            $this->assertStringContainsString('email: This value is not a valid email address.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }
}
