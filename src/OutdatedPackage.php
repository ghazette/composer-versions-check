<?php

namespace SLLH\ComposerVersionsCheck;

use Composer\Package\Link;
use Composer\Package\PackageInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class OutdatedPackage
{
    private PackageInterface $actual;
    private PackageInterface $last;
    private array $links = [];

    public function __construct(
        PackageInterface $actual,
        PackageInterface $last,
        ?array $links = null
    ) {
        $this->actual = $actual;
        $this->last = $last;
        $this->links = $links ?? [];
    }

    public function getActual(): PackageInterface
    {
        return $this->actual;
    }

    public function getLast(): PackageInterface
    {
        return $this->last;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }
}
