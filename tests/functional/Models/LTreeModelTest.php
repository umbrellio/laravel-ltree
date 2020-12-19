<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Models;

use Generator;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeModelTest extends LTreeBaseTestCase
{
    /**
     * @test
     * @dataProvider provideLevels
     */
    public function getLtreeLevel(string $path, int $level): void
    {
        $this->assertSame($level, $this ->findNodeByPath($path) ->getLtreeLevel());
    }

    public function provideLevels(): Generator
    {
        yield 'root' => [
            'path' => '1',
            'level' => 1,
        ];
        yield 'second-level' => [
            'path' => '1.2',
            'level' => 2,
        ];
        yield 'third-level' => [
            'path' => '1.2.5',
            'level' => 3,
        ];
    }

    /**
     * @test
     * @dataProvider providePaths
     */
    public function parentsOf(array $paths, int $expectedCount): void
    {
        $this->assertCount($expectedCount, CategoryStub::parentsOf($paths)->get());
    }

    public function providePaths(): Generator
    {
        yield 'single_as_array' => [
            'paths' => ['11.12'],
            'expected' => 2,
        ];
        yield 'all_as_array' => [
            'paths' => ['11.12', '1.2.5'],
            'expected' => 5,
        ];
    }

    /**
     * @test
     */
    public function root(): void
    {
        $node = $this->findNodeByPath('1.2.5');
        $roots = $node::root()->get();
        foreach ($roots as $root) {
            $this->assertNull($root->parent_id);
        }
    }

    /**
     * @test
     */
    public function ancestors(): void
    {
        $root = $this->getRoot();
        $node6 = $this->findNodeByPath('1.3.6');
        $node2 = $this->findNodeByPath('1.2');
        $this->assertSame(3, $root::ancestorsOf($node6)->get()->count());
        $this->assertTrue($node2->isParentOf(5));
    }

    /**
     * @test
     */
    public function getAncestorByLevel(): void
    {
        $root = $this->getRoot();
        $node2 = $this->findNodeByPath('1.2');
        $node5 = $this->findNodeByPath('1.2.5');
        $node6 = $this->findNodeByPath('1.3.6');
        $node8 = $this->findNodeByPath('1.3.6.8');
        $descendants = $root::descendantsOf($root)->withoutSelf(1);
        $this->assertGreaterThan(0, $descendants->count());
        $descendants->each(static function ($descendant) use ($root) {
            $descendant->getAncestorByLevel($root->getKey());
        });

        $this->assertSame($node5->getAncestorByLevel(2)->getKey(), $node2->getKey());
        $this->assertSame($node8->getAncestorByLevel(3)->getKey(), $node6->getKey());
    }

    /**
     * @test
     */
    public function children(): void
    {
        $node11 = $this->findNodeByPath('11');

        $this->assertSame(1, $node11->ltreeChildren->count());
        $this->assertSame(12, $node11->ltreeChildren->first()->getKey());
    }
}
