<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtConfigDefinition\Model;

use Exception;
use GhostUnicorns\CrtBase\Api\CrtConfigInterface;
use GhostUnicorns\CrtBase\Exception\CrtException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\DataInterface;
use Psr\Log\LoggerInterface;

class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @param DataInterface $data
     */
    public function __construct(
        DataInterface $data
    ) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getAllCollectors(): array
    {
        return $this->getCrtTypes('collector');
    }

    /**
     * @param $type
     * @return array
     */
    private function getCrtTypes($type): array
    {
        $typesNode = $this->get('config/0/type');

        $types = [];
        foreach ($typesNode as $typeNode) {
            if (!array_key_exists($type . 's', $typeNode)) {
                break;
            }
            $typesNode = $typeNode[$type . 's']['0'];

            if (!array_key_exists($typeNode['name'], $types)) {
                $types[$typeNode['name']] = [];
            }

            $nodes = [];
            foreach ($typesNode[$type] as $node) {
                if (!array_key_exists('logger', $node)) {
                    $node['logger'] = ObjectManager::getInstance()->get($typeNode['logger']);
                } else {
                    $node['logger'] = ObjectManager::getInstance()->get($node['logger']);
                }
                if (!array_key_exists('config', $node)) {
                    $node['config'] = ObjectManager::getInstance()->get($typeNode['config']);
                } else {
                    $node['config'] = ObjectManager::getInstance()->get($node['config']);
                }
                $node['model'] = ObjectManager::getInstance()->create(
                    $node['model'],
                    [
                        'logger' => $node['logger'],
                        'config' => $node['config']
                    ]
                );
                $nodes[$node['name']] = $node;
            }

            uasort($nodes, function ($a, $b) {
                return $a['sortOrder'] - $b['sortOrder'];
            });

            $types[$typeNode['name']] = $nodes;
        }

        return $types;
    }

    /**
     * @param $typeName
     * @return array
     * @throws CrtException
     */
    public function getType($typeName): array
    {
        $typesNode = $this->get('config/0/type');

        $type = array_filter($typesNode, function ($t) use ($typeName) {
            return  $t['name'] === $typeName;
        });

        if (!$type) {
            throw new CrtException(__('There is not a Crt Type with name: %1', $typeName));
        }

        return current($type);
    }

    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     */
    public function get(string $key = null, string $default = null): array
    {
        return $this->data->get($key, $default) ?? [];
    }

    /**
     * @return array
     */
    public function getAllRefiners(): array
    {
        try {
            return $this->getCrtTypes('refiner');
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @return array
     */
    public function getAllTransferors(): array
    {
        try {
            return $this->getCrtTypes('transferor');
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param $type
     * @return CrtConfigInterface
     * @throws CrtException
     */
    public function getTypeConfig($type): CrtConfigInterface
    {
        $typeNode = $this->getType($type);
        if (!array_key_exists('config', $typeNode)) {
            throw new CrtException(__('Missing default config for crt type: %1', $type));
        }
        $config = $typeNode['config'];
        return ObjectManager::getInstance()->get($config);
    }

    /**
     * @param $type
     * @return LoggerInterface
     * @throws CrtException
     */
    public function getTypeLogger($type): LoggerInterface
    {
        $typesNode = $this->get('config/0/type');
        foreach ($typesNode as $typeNode) {
            if ($typeNode['name'] === $type) {
                $logger = $typeNode['logger'];
                break;
            }
        }
        if (!isset($logger)) {
            throw new CrtException(__('Missing default logger for crt type: %1', $type));
        }
        return ObjectManager::getInstance()->get($logger);
    }
}
