<?php

declare(strict_types=1);

namespace Morph\Tests\Fixtures;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Exposed
{
}
