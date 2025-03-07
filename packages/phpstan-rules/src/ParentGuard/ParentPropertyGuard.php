<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\ParentGuard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Symplify\Astral\Naming\SimpleNameResolver;

final class ParentPropertyGuard
{
    public function __construct(
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    public function isPropertyGuarded(Property $property, Scope $scope): bool
    {
        $propertyName = $this->simpleNameResolver->getName($property);
        if ($propertyName === null) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflectoin) {
            if (! $parentClassReflectoin->hasNativeProperty($propertyName)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
