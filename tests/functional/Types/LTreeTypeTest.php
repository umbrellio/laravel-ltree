<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\tests\FunctionalTestCase;
use Umbrellio\LTree\Types\LTreeType;

class LTreeTypeTest extends FunctionalTestCase
{
    private AbstractPlatform $abstractPlatform;
    private LTreeType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new LTreeType();
        $this->abstractPlatform = new PostgreSQLPlatform();
    }

    #[Test]
    public function getSQLDeclaration(): void
    {
        $this->assertSame(LTreeType::TYPE_NAME, $this->type->getSQLDeclaration([], $this->abstractPlatform));
    }

    #[Test]
    #[DataProvider('providePHPValues')]
    public function convertToPHPValue($value, $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, $this->abstractPlatform));
    }

    public static function provideDatabaseValues(): Generator
    {
        yield [null, null];
        yield ['1.2.3', [1, 2, 3]];
        yield [1, [1]];
    }

    #[Test]
    #[DataProvider('provideDatabaseValues')]
    public function convertToDatabaseValue($value, $expected): void
    {
        $this->assertSame($expected, $this->type->convertToPHPValue($value, $this->abstractPlatform));
    }

    public static function providePHPValues(): Generator
    {
        yield [null, null];
        yield [1, '1'];
        yield [[1], '1'];
        yield [[1, 2, 3], '1.2.3'];
    }

    #[Test]
    public function getTypeName(): void
    {
        $this->assertSame(LTreeType::TYPE_NAME, $this->type->getName());
    }
}
