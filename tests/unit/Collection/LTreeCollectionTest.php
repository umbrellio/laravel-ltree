<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\Helpers;

use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Umbrellio\LTree\Collections\LTreeCollection;
use Umbrellio\LTree\Interfaces\LTreeModelInterface;
use Umbrellio\LTree\tests\_data\Models\CategoryStubResourceCollection;
use Umbrellio\LTree\tests\TestCase;
use Umbrellio\LTree\Traits\LTreeModelTrait;

class LTreeCollectionTest extends TestCase
{
    public function provideItems(): Generator
    {
        yield 'single_sub_tree' => [
            'items' => [
                [
                    'id' => 1,
                    'path' => '1',
                    'parent_id' => null,
                ],
                [
                    'id' => 2,
                    'path' => '1.2',
                    'parent_id' => 1,
                ],
                [
                    'id' => 3,
                    'path' => '2.3',
                    'parent_id' => 2,
                ],
                [
                    'id' => 4,
                    'path' => '2.4',
                    'parent_id' => 2,
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideItems
     */
    public function lTreeResource(array $items): void
    {
        $collection = new CategoryStubResourceCollection(
            $this->getLtreeModelsCollection($items),
            [
                'id' => 'desc',
            ]
        );

        $tree = $collection->toArray(new Request());

        $this->assertSame(1, $tree[0]['id']);
        $this->assertSame(2, $tree[0]['children'][0]->id);
        $this->assertSame(4, $tree[0]['children'][0]['children'][0]->id);
        $this->assertSame(3, $tree[0]['children'][0]['children'][1]->id);
    }

    private function getLtreeModelsCollection(array $items)
    {
        $collection = new LTreeCollection();
        foreach ($items as $item) {
            $collection->add($this->getLtreeModel($item));
        }
        return $collection;
    }

    private function getLtreeModel($data)
    {
        return new class($data) extends Model implements LTreeModelInterface {
            use LTreeModelTrait;

            protected $fillable = ['id', 'path', 'parent_id', 'name'];
        };
    }
}
