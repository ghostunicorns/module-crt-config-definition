<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtConfigDefinition\Model;

use GhostUnicorns\CrtBase\Api\CrtConfigInterface;
use Psr\Log\LoggerInterface;

interface ConfigInterface
{
    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     */
    public function get(string $key = null, string $default = null): array;

    public function getType(string $typeName): array;

    public function getAllCollectors(): array;

    public function getAllRefiners(): array;

    public function getAllTransferors(): array;

    public function getTypeConfig(string $type): CrtConfigInterface;

    public function getTypeLogger(string $type): LoggerInterface;
}
