<?php

declare(strict_types=1);

namespace Umbrellio\LTree\tests\functional\Resources;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Umbrellio\LTree\tests\_data\Models\CategoryStub;
use Umbrellio\LTree\tests\_data\Models\CategoryStubResourceCollection;
use Umbrellio\LTree\tests\LTreeBaseTestCase;

class LTreeResourceTest extends LTreeBaseTestCase
{
    #[Test]
    public function resources(): void
    {
        $resource = new CategoryStubResourceCollection(
            CategoryStub::query()->whereKey([7, 12])->get(),
            [
                'id' => 'desc',
            ]
        );
        $this->assertSame($resource->toArray(new Request()), [
            [
                'id' => 11,
                'path' => '11',
                'children' => [
                    [
                        'id' => 12,
                        'path' => '11.12',
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 1,
                'path' => '1',
                'children' => [
                    [
                        'id' => 3,
                        'path' => '1.3',
                        'children' => [
                            [
                                'id' => 7,
                                'path' => '1.3.7',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
