<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Tests\Api;

use App\Api\Routes;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Test\AbstractApiTest;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Proxy;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UsersTest extends AbstractApiTest
{
    use ReloadDatabaseTrait;

    public function testGetCollectionUnauthorized()
    {
        // Send "GET /users" request adn check that it doesn't work without token and with invalid token
        $this->assertUnauthorizedRequest(Routes::USERS_COLLECTION);
    }

    public function testGetItemUnauthorized()
    {
        // Create a user
        $user = UserFactory::new()->create();
        // Send "GET /users" request adn check that it doesn't work without token and with invalid token
        $this->assertUnauthorizedRequest([Routes::USER_ITEM, $user->getId()]);
    }

    public function testCreteUnauthorized()
    {
        // Send "GET /users" request adn check that it doesn't work without token and with invalid token
        $this->assertUnauthorizedRequest(Routes::CRETE_USER, self::getUserData());
    }

    public function testUpdateUnauthorized()
    {
        // Create a user
        $user = UserFactory::new()->create();
        // Send "GET /users" request adn check that it doesn't work without token and with invalid token
        $this->assertUnauthorizedRequest([Routes::UPDATE_USER, $user->getId()], self::getUserData());
    }

    public function testDeleteUnauthorized()
    {
        // Create a user
        $user = UserFactory::new()->create();
        // Send "GET /users" request adn check that it doesn't work without token and with invalid token
        $this->assertUnauthorizedRequest([Routes::DELETE_USER, $user->getId()]);
    }

    public function testGetCollection()
    {
        // Login as admin
        $this->login();

        static $usersCount = 15;

        // Create 15 users
        /** @var array<Proxy> $users */
        $users = UserFactory::createMany($usersCount);

        // Send "GET /users" request
        $this->send(Routes::USERS_COLLECTION);
        // Check response HTTP status
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response body, check users count in response and check data from the first and the last user
        $usersData = $this->getResponseParams();
        $this->assertEquals($usersCount, count($usersData));
        $this->assertUserData(array_shift($users)->object(), array_shift($usersData));
        $this->assertUserData(end($users)->object(), end($usersData));
    }

    public function testGetItem()
    {
        // Create a user
        $user = UserFactory::new()->create();

        // Login as admin
        $this->login();

        // Send "GET /users/<user_id>" request
        $this->send([Routes::USER_ITEM, $user->getId()]);
        // Check the response status code and data
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertUserData($user->object(), $this->getResponseParams());

        // Tet 404 response
        $this->send([Routes::USER_ITEM, 1234567]);
        $this->assertErrorResponse(Response::HTTP_NOT_FOUND);
    }

    public function testSuccessfulCreateItemFullData()
    {
        $createData = self::getUserData();

        // Login as admin
        $this->login();
        // Send "PUT /users/<user_id>" request
        $this->send(Routes::CRETE_USER, $createData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);

        // Get updated user object from DB and compare it from old object adn response data
        $this->assetCreatedUser($createData, $responseParams);
    }

    public function testSuccessfulCreateItemPartialData()
    {
        $partialData = self::getUserData();
        unset($partialData['uuid']);

        // Login as admin
        $this->login();

        // <-- No uuid -->
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetCreatedUser($partialData, $responseParams);
    }

    public function testCreateExistedItem()
    {
        // Create a user
        $user = UserFactory::new()->create();
        $createData = self::getUserData();

        // Login as admin
        $this->login();

        // <-- Existed uuid and email -->
        $invalidData = array_merge($createData, ['email' => $user->getEmail(), 'uuid' => $user->getUuid()]);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $invalidData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, ['uuid', 'email']);

        // <-- Existed uuid -->
        $invalidData = array_merge($createData, ['uuid' => $user->getUuid()]);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $invalidData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'uuid');

        // <-- Existed email -->
        $invalidData = array_merge($createData, ['email' => $user->getEmail()]);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $invalidData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'email');
    }

    public function testCreateItemWithIncorrectData()
    {
        $createData = self::getUserData();

        // Login as admin
        $this->login();

        // <-- Empty password -->
        $incorrectData = $createData;
        unset($incorrectData['password']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'password');

        // <-- Empty firstName -->
        $incorrectData = $createData;
        unset($incorrectData['firstName']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'firstName');

        // <-- Empty lastName -->
        $incorrectData = $createData;
        unset($incorrectData['lastName']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'lastName');

        // <-- Empty password, firstName and lastName -->
        $incorrectData = $createData;
        unset($incorrectData['password'], $incorrectData['firstName'], $incorrectData['lastName']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, ['password', 'firstName', 'lastName']);

        // <-- Short UUID -->
        $incorrectData = array_merge($createData, ['uuid' => '7ba1bd67-a0f3-489a-90ff-c1bbad6d652']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'uuid');

        // <-- Long UUID -->
        $incorrectData = array_merge($createData, ['uuid' => '7ba1bd67-a0f3-489a-90ffc-c1bbad6d6524']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'uuid');

        // <-- Invalid email -->
        $incorrectData = array_merge($createData, ['email' => 'invalid_email']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'email');

        // <-- Short password -->
        $incorrectData = array_merge($createData, ['password' => '123']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'password');

        // <-- Long password -->
        $incorrectData = array_merge($createData, ['password' => '1234567890123456789012345678901234567']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'password');

        // <-- Short firstName -->
        $incorrectData = array_merge($createData, ['firstName' => 'A']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'firstName');

        // <-- Long firstName -->
        $incorrectData = array_merge($createData, ['firstName' => '1234567890123456789012345678901234567']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'firstName');

        // <-- Short lastName -->
        $incorrectData = array_merge($createData, ['lastName' => 'A']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'lastName');

        // <-- Long lastName -->
        $incorrectData = array_merge($createData, ['lastName' => '1234567890123456789012345678901234567']);
        // Send "POST /users" request
        $this->send(Routes::CRETE_USER, $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'lastName');
    }

    public function testSuccessfulUpdateItemFullData()
    {
        // Create a user and copy object with current attributes
        $user = UserFactory::new()->create();
        /** @var User $oldUser */
        $oldUser = clone $user->object();
        $updateData = self::getUserData();

        // Login as admin
        $this->login();

        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $updateData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);

        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $updateData, $responseParams);
    }

    public function testSuccessfulUpdateItemPartialData()
    {
        // Create a user and copy object with current attributes
        $user = UserFactory::new()->create();
        /** @var User $oldUser */
        $oldUser = clone $user->object();
        $updateData = self::getUserData();

        // Login as admin
        $this->login();

        // <-- No uuid -->
        $partialData = $updateData;
        unset($partialData['uuid']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- No email -->
        $partialData = $updateData;
        unset($partialData['email']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- No password -->
        $partialData = $updateData;
        unset($partialData['password']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- No firstName -->
        $partialData = $updateData;
        unset($partialData['firstName']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- No lastName -->
        $partialData = $updateData;
        unset($partialData['lastName']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only uuid -->
        $partialData = ['uuid' => $updateData['uuid']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only email -->
        $partialData = ['email' => $updateData['email']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only password -->
        $partialData = ['password' => $updateData['password']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only firstName -->
        $partialData = ['firstName' => $updateData['firstName']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only lastName -->
        $partialData = ['lastName' => $updateData['lastName']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only firstName and lastName -->
        $partialData = ['firstName' => $updateData['firstName'], 'lastName' => $updateData['lastName']];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only firstName, lastName and password -->
        $partialData = [
            'firstName' => $updateData['firstName'],
            'lastName' => $updateData['lastName'],
            'password' => $updateData['password'],
        ];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);

        // <-- Only firstName, lastName, password and email -->
        $partialData = [
            'firstName' => $updateData['firstName'],
            'lastName' => $updateData['lastName'],
            'password' => $updateData['password'],
            'email' => $updateData['email'],
        ];
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $partialData);
        // Check the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Get response params
        $responseParams = $this->getResponseParams();
        $this->assertIsArray($responseParams);
        // Get updated user object from DB and compare it from old object adn response data
        $this->assetUpdatedUser($oldUser, $partialData, $responseParams);
    }

    public function testUpdateNotExistedItem()
    {
        // Login as admin
        $this->login();
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, 1234567], []);
        // Check the response status code
        $this->assertErrorResponse(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateItemWithIncorrectData()
    {
        // Create a user and copy object with current attributes
        $user = UserFactory::new()->create();
        $updateData = self::getUserData();

        // Login as admin
        $this->login();
        // <-- Short UUID -->
        $incorrectData = array_merge($updateData, ['uuid' => '7ba1bd67-a0f3-489a-90ff-c1bbad6d652']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'uuid');

        // <-- Long UUID -->
        $incorrectData = array_merge($updateData, ['uuid' => '7ba1bd67-a0f3-489a-90ffc-c1bbad6d6524']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'uuid');

        // <-- Invalid email -->
        $incorrectData = array_merge($updateData, ['email' => 'invalid_email']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'email');

        // <-- Short password -->
        $incorrectData = array_merge($updateData, ['password' => '123']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'password');

        // <-- Long password -->
        $incorrectData = array_merge($updateData, ['password' => '1234567890123456789012345678901234567']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'password');

        // <-- Short firstName -->
        $incorrectData = array_merge($updateData, ['firstName' => 'A']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'firstName');

        // <-- Long firstName -->
        $incorrectData = array_merge($updateData, ['firstName' => '1234567890123456789012345678901234567']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'firstName');

        // <-- Short lastName -->
        $incorrectData = array_merge($updateData, ['lastName' => 'A']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'lastName');

        // <-- Long lastName -->
        $incorrectData = array_merge($updateData, ['lastName' => '1234567890123456789012345678901234567']);
        // Send "PUT /users/<user_id>" request
        $this->send([Routes::UPDATE_USER, $user->getId()], $incorrectData);
        // Check the response status code and error message
        $this->assertErrorResponse(Response::HTTP_UNPROCESSABLE_ENTITY, 'lastName');
    }

    public function testDeleteItem()
    {
        // Create user
        /** @var User $user */
        $user = UserFactory::createOne();

        // Login as admin
        $this->login();

        // Send "DELETE /users/<user_id>" request and check is response code is 204 and response content is empty
        $this->assertDeleteRequest([Routes::DELETE_USER, $user->getId()], User::class, $user->getId());

        // Send "DELETE /users/<user_id>" request and check is response code is 204 and response content is empty
        //  for deleted user and for some random ID
        $this->send([Routes::DELETE_USER, $user->getId()]);
        $this->assertErrorResponse(Response::HTTP_NOT_FOUND, 'Not Found');
        $this->send([Routes::DELETE_USER, 123456]);
        $this->assertErrorResponse(Response::HTTP_NOT_FOUND, 'Not Found');
    }

    private static function getUserData(): array
    {
        return [
            'uuid' => (string) Uuid::v4(),
            'email' => 'updated_email@mail.com',
            'password' => 'updated_password',
            'firstName' => 'Updated_first_name',
            'lastName' => 'Updated_last_name',
        ];
    }

    private function assetCreatedUser(array $createData, array $responseParams): void
    {
        /** @var User $createdUser */
        $createdUser = $this->em->getRepository(User::class)->findOneByEmail($createData['email']);
        $this->assertNotNull($createdUser);
        $this->assertIsInt($createdUser->getId());
        $this->assertNotNull($createdUser->getUuid());
        $this->assertIsString($createdUser->getUuid());
        $this->assertMatchesRegularExpression("/^[\w\d]+-[\w\d]+-[\w\d]+-[\w\d]+-[\w\d]+$/", $createdUser->getUuid());
        $this->assertNotNull($createdUser->getEmail());
        $this->assertIsString($createdUser->getEmail());
        $this->assertNotNull($createdUser->getPassword());
        $this->assertIsString($createdUser->getPassword());

        $password = $createData['password'] ?? null;
        unset($createData['password']);

        $this->assertUserAttributes($createdUser, $createData, $password);
        $this->assertUserData($createdUser, $responseParams);
    }

    private function assetUpdatedUser(User $oldUser, array $updateData, array $responseParams): void
    {
        /** @var User $updatedUser */
        $updatedUser = $this->em->getRepository(User::class)->find($oldUser->getId());
        $this->em->refresh($updatedUser);
        $this->assertNotNull($updatedUser);
        $this->assertEquals($oldUser->getId(), $updatedUser->getId());

        $password = $updateData['password'] ?? null;
        unset($updateData['password']);
        if ($password !== null) {
            $this->assertNotEquals($updatedUser->getPassword(), $password);
        }
        foreach ($updateData as $name => $value) {
            $getter = 'get' . ucfirst($name);
            if (method_exists($updatedUser, $getter)) {
                $this->assertNotEquals($oldUser->$getter(), $updatedUser->$getter());
                $this->assertEquals($value, $updatedUser->$getter());
            }
        }

        $this->assertUserData($updatedUser, $responseParams, $password);
    }

    private function assertUserData(User $user, array $data, ?string $password = null): void
    {
        $this->assertIsInt($data['id']);
        $this->assertIsString($data['uuid']);
        $this->assertMatchesRegularExpression("/^[\w\d]+-[\w\d]+-[\w\d]+-[\w\d]+-[\w\d]+$/", $data['uuid']);
        $this->assertEquals(36, strlen($data['uuid']));
        $this->assertArrayNotHasKey('password', $data);
        $this->assertUserAttributes($user, $data, $password);
    }

    private function assertUserAttributes(User $user, array $attributes, ?string $password = null): void
    {
        foreach ($attributes as $name => $value) {
            $getter = 'get' . ucfirst($name);
            if (method_exists($user, $getter)) {
                $this->assertEquals($value, $user->$getter());
            }
        }
        // Check password
        if ($password !== null) {
            /** @var PasswordHasherFactoryInterface $hasher */
            $hasherFactory = $this->loadComponent(PasswordHasherFactoryInterface::class);
            $this->assertTrue($hasherFactory->getPasswordHasher($user)->verify($user->getPassword(), $password));
        }
    }
}