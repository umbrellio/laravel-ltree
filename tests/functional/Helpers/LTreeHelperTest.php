<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Helpers;

use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeHelperTest extends LTreeBaseTestCase
{
    #[Test]
    public function createViaServiceRoot(): void
    {
        $node = $this->createCategory([
            'id' => 15,
            'path' => null,
            'parent_id' => null,
        ]);
        $this->assertSame([], $node->getLtreePath());
        $this->ltreeService->createPath($node);
        $this->assertSame('15', $node->getLtreePath(LTreeModelInterface::AS_STRING));
    }

    #[Test]
    public function moveSubtrees(): void
    {
        $nodes = $this->getCategories();
        $root = $this->getRoot();
        /** @var CategoryStub $someNode */
        $someNode = $nodes->find(11);
        $parentColumn = $root->getLtreeParentColumn();
        $this->assertSame(1, $root::descendantsOf($someNode)->withoutSelf(11)->count());
        $root->update([
            $parentColumn => 11,
        ]);
        $this->ltreeService->updatePath($root);
        $this->assertSame(11, $root::descendantsOf($someNode)->withoutSelf(11)->count());
        $this->assertSame(11, $root->getLtreeParentId());
    }

    #[Test]
    public function proxyColumns(): void
    {
        $nodeMoscow = $this->findNodeByPath('1.3');
        $nodeRussia = $this->findNodeByPath('1');

        $this->assertSame('Moscow', $nodeMoscow->name);

        $nodeRussia->name = 'New Russia';
        $nodeRussia->save();
        $this->ltreeService->updatePath($nodeRussia);

        $nodeMoscow->refresh();
        $this->assertSame('New Russia', $nodeMoscow->name);

        $nodeRussia->name = null;
        $nodeRussia->save();
        $this->ltreeService->updatePath($nodeRussia);

        $nodeMoscow->refresh();
        $this->assertNull($nodeMoscow->name);
    }

    #[Test]
    public function deleteRoot(): void
    {
        $root = $this->getRoot();

        $this->assertTrue($root::descendantsOf($root)->exists());
        $root::descendantsOf($root)->delete();
        $this->assertFalse($root::descendantsOf($root)->exists());
    }

    #[Test]
    public function deleteSubtree(): void
    {
        $root = $this->getRoot();

        $this->assertSame(9, $root::descendantsOf($root)->withoutSelf(1)->count());
        $root::descendantsOf($root)->withoutSelf(1)->delete();
        $this->assertFalse($root::descendantsOf($root)->withoutSelf(1)->exists());
        $this->assertSame(1, $root::descendantsOf($root)->count());
    }

    #[Test]
    public function deleteViaServiceSubtree(): void
    {
        $root = $this->getRoot();

        $this->assertSame(9, $root::descendantsOf($root)->withoutSelf(1)->count());
        $root->update([
            'is_deleted' => 1,
        ]);
        $root->delete();
        $root->refresh();
        $this->ltreeService->dropDescendants($root);
        $this->assertFalse($root::whereKey($root->getKey())->exists());
    }
}
