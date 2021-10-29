<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\CrtConfigDefinition\Model\Config\Converter;

use DOMCharacterData;
use DOMDocument;
use DOMNode;
use Magento\Framework\Config\ConverterInterface;

/**
 * A converter of reports configuration.
 *
 * Converts configuration data stored in XML format into corresponding PHP array.
 */
class Xml implements ConverterInterface
{
    /**
     * Converts XML document into corresponding array.
     *
     * @param DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        return $this->convertNode($source);
    }

    /**
     * Converts XML node into corresponding array.
     *
     * @param DOMNode $source
     * @return array|string
     */
    private function convertNode(DOMNode $source)
    {
        $result = [];
        if ($source->hasAttributes()) {
            $attrs = $source->attributes;
            foreach ($attrs as $attr) {
                $result[$attr->name] = $attr->value;
            }
        }
        if ($source->hasChildNodes()) {
            $children = $source->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            foreach ($children as $child) {
                if ($child instanceof DOMCharacterData) {
                    continue;
                }
                $result[$child->nodeName][] = $this->convertNode($child);
            }
        }
        return $result;
    }
}
