<?php declare(strict_types=1);
/**
 * Created 2021-11-26
 * Author Dmitry Kushneriov
 */

namespace App\Test;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractUnitTest extends KernelTestCase
{
    protected ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $this->em = $this->loadComponent('doctrine')->getManager();
    }

    /**
     * @param string $componentID
     *
     * @return object|null
     */
    protected function loadComponent(string $componentID)
    {
        return static::getContainer()->get($componentID);
    }

    /**
     * @param MockObject $mockedRepository
     *
     * @return EntityManagerInterface
     */
    protected function mockEntityManagerForRepository(MockObject $mockedRepository): MockObject
    {
        $mockedEm = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $mockedEm->method('getRepository')->willReturn($mockedRepository);
        return $mockedEm;
    }
}