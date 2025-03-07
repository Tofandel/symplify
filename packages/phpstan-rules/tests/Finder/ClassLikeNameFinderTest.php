<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Finder;

use Iterator;
use PHPStan\Testing\PHPStanTestCase;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symplify\PHPStanRules\Finder\ClassLikeNameFinder;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngine\PriceProviderInterface;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngine\ProductProviderInterface;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngine\SkipFallbackPriceProviderInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngineImpl\SkipCustomerPriceProviderInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngineImpl\SkipCustomerProductProviderInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngineImpl\SkipDealerPriceProviderInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngineImpl\SkipDealerProductProviderInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Model\Customer\Request\SkipCustomerRequestModelInAuthorizedNamespace;
use Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Model\Order\Request\SkipOrderRequestModelInAuthorizedNamespace;

final class ClassLikeNameFinderTest extends PHPStanTestCase
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedClassLikeNames
     */
    public function test(string $namespaceWildcardPattern, array $expectedClassLikeNames): void
    {
        $classLikeNameFinder = self::getContainer()->getByType(ClassLikeNameFinder::class);

        $actualClassLikeNames = $classLikeNameFinder->getClassLikeNamesMatchingNamespacePattern(
            $namespaceWildcardPattern
        );

        sort($expectedClassLikeNames);
        sort($actualClassLikeNames);

        $this->assertSame($expectedClassLikeNames, $actualClassLikeNames);
    }

    /**
     * @return Iterator<string,array<string|string[]>>
     */
    public function provideData(): Iterator
    {
        yield 'it should find all classes in PriceEngine directory' => [
            'Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\Component\PriceEngine\**',
            [
                PriceProviderInterface::class,
                ProductProviderInterface::class,
                SkipFallbackPriceProviderInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes in PriceEngine and PriceEngineImpl directory' => [
            'Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\*\PriceEngine**',
            [
                PriceProviderInterface::class,
                ProductProviderInterface::class,
                SkipFallbackPriceProviderInAuthorizedNamespace::class,
                SkipCustomerPriceProviderInAuthorizedNamespace::class,
                SkipCustomerProductProviderInAuthorizedNamespace::class,
                SkipDealerPriceProviderInAuthorizedNamespace::class,
                SkipDealerProductProviderInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes in Model\Customer\Request and Model\Order\Request directories' => [
            'Symplify\PHPStanRules\Tests\Rules\ClassExtendingExclusiveNamespaceRule\Fixture\App\**\Request\**',
            [
                SkipCustomerRequestModelInAuthorizedNamespace::class,
                SkipOrderRequestModelInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes whose class name starts with SkipCustomer' => [
            '**\SkipCustomer*',
            [
                SkipCustomerPriceProviderInAuthorizedNamespace::class,
                SkipCustomerProductProviderInAuthorizedNamespace::class,
                SkipCustomerRequestModelInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes whose class name matches Skip*Price*' => [
            '**\Skip*Price*',
            [
                SkipCustomerPriceProviderInAuthorizedNamespace::class,
                SkipDealerPriceProviderInAuthorizedNamespace::class,
                SkipFallbackPriceProviderInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes whose class name matches S??p*Price*' => [
            '**\S??p*Price*',
            [
                SkipCustomerPriceProviderInAuthorizedNamespace::class,
                SkipDealerPriceProviderInAuthorizedNamespace::class,
                SkipFallbackPriceProviderInAuthorizedNamespace::class,
            ],
        ];

        yield 'it should find all classes in vendor for Symfony\Component\Finder\**' => [
            'Symfony\Component\Finder\**\*Exception',
            [AccessDeniedException::class, DirectoryNotFoundException::class],
        ];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../config/included_services.neon'];
    }
}
