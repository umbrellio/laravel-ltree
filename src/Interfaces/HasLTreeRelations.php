<?php

declare(strict_types=1);

namespace Umbrellio\LTree\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasLTreeRelations
{
    public function ltreeParent(): BelongsTo;
    public function ltreeChildren(): HasMany;
}
