<?php
/**
 * Created 2021-10-11
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use App\Test\Constraint\ArraySubset;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception as HttpException;
use Symfony\Component\HttpClient\Exception\JsonException;

trait ApiTestAssertionsTrait
{
    use RequestParamsTrait;

    /**
     * Asserts that the retrieved JSON contains the specified subset.
     *
     * This method delegates to static::assertArraySubset().
     *
     * @param array|string $subset
     * @param bool $checkForObjectIdentity
     * @param string $message
     *
     * @throws HttpException\ClientExceptionInterface
     * @throws HttpException\DecodingExceptionInterface
     * @throws HttpException\RedirectionExceptionInterface
     * @throws HttpException\ServerExceptionInterface
     * @throws HttpException\TransportExceptionInterface
     * @throws JsonException
     * @throws \Exception
     */
    public function assertJsonContains($subset, bool $checkForObjectIdentity = true, string $message = ''): void
    {
        if (is_string($subset)) {
            $subset = json_decode($subset, true);
        }
        if (!is_array($subset)) {
            throw new \InvalidArgumentException('$subset must be array or string (JSON array or JSON object)');
        }

        $this->assertArraySubset($subset, $this->getResponseParams(), $checkForObjectIdentity, $message);
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * Imported from dms/phpunit-arraysubset, because the original constraint has been deprecated.
     *
     * @copyright Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright Rafael Dohms <rdohms@gmail.com>
     *
     * @param $subset
     * @param $array
     * @param bool $checkForObjectIdentity
     * @param string $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);

        $this->assertThat($array, $constraint, $message);
    }

    public function assertErrorResponse(int $statusCode, string|array $errorMsgPart = null, string $errorMsgIndex = 'detail'): void
    {
        $this->assertResponseStatusCodeSame($statusCode);
        $responseParams = $this->getResponseParams();
        $this->assertArrayHasKey('type', $responseParams);
        $this->assertArrayHasKey('title', $responseParams);
        $this->assertArrayHasKey('detail', $responseParams);
        $this->assertIsString($responseParams['type']);
        $this->assertIsString($responseParams['title']);
        $this->assertIsString($responseParams['detail']);

        if ($errorMsgPart !== null) {
            if (!is_array($errorMsgPart)) {
                $errorMsgPart = [$errorMsgPart];
            }
            foreach ($errorMsgPart as $part) {
                $this->assertStringContainsString($part, $responseParams[$errorMsgIndex]);
            }
        }
    }

    public function assertUnauthorizedRequest(string|array $request, array $params = [])
    {
        // <-- Without token -->
        $this->send($request);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertJsonContains(['message' => 'JWT Token not found']);

        // <-- With incorrect token -->
        $this->setToken('INVALID_TOKEN');
        $this->send($request, $params);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertJsonContains(['message' => 'Invalid JWT Token']);
    }

    public function assertDeleteRequest(string|array $request, ?string $entityClass = null, int|string|array $searchParams = [])
    {
        $this->send($request);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($this->getClient()->getResponse()->getContent());
        if ($entityClass !== null && !empty($searchParams)) {
            // Check is entity doesn't presented in database
            $deletedEntity = $this->em->getRepository($entityClass)->find($searchParams);
            $this->assertNull($deletedEntity);
        }
    }
}