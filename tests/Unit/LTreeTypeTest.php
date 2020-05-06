<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Generator;
use Umbrellio\LTree\Types\LTreeType;

class LTreeTypeTest extends TestCase
{
    /**
     * @var AbstractPlatform
     */
    private $abstractPlatform;

    /**
     * @var LTreeType
     */
    private $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = $this
            ->getMockBuilder(LTreeType::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractPlatform = $this->getMockForAbstractClass(AbstractPlatform::class);
    }

    /**
     * @test
     */
    public function getSQLDeclaration(): void
    {
        $this->assertSame(LTreeType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    /**
     * @dataProvider providePHPValues
     * @test
     */
    public function convertToPHPValue($value, $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, $this->abstractPlatform));
    }

    public function provideDatabaseValues(): Generator
    {
        yield [null, null];
        yield ['1.2.3', [1, 2, 3]];
        yield [1, [1]];
    }

    /**
     * @dataProvider provideDatabaseValues
     * @test
     */
    public function convertToDatabaseValue($value, $expected): void
    {
        $this->assertSame($expected, $this->type->convertToPHPValue($value, $this->abstractPlatform));
    }

    public function providePHPValues(): Generator
    {
        yield [null, null];
        yield [1, '1'];
        yield [[1], '1'];
        yield [[1, 2, 3], '1.2.3'];
    }

    /**
     * @test
     */
    public function getTypeName(): void
    {
        $this->assertSame(LTreeType::TYPE_NAME, $this->type->getName());
    }
}
