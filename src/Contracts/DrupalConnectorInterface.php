<?php
/**
 * @file
 * ${fileDescription}
 */

namespace App\Contracts;

use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;

interface DrupalConnectorInterface
{
    /**
     * Gets a node from the d.o. api.
     *
     * @param string $nid
     *
     * @return bool|\Hussainweb\DrupalApi\Entity\Collection\EntityCollection|\Hussainweb\DrupalApi\Entity\Entity
     */
    public function getNode(string $nid);

    /**
     * Gets the name for an issue tag, from the cache if possible.
     *
     * @param string $tid
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTag(string $tid): string;

    /**
     * Retrieves an issue tag from D.o.
     *
     * @param CacheItemInterface $item
     * @return string
     */
    public function retrieveTag(CacheItemInterface $item): string;

    /**
     * Gets a comment from d.o.
     * @param $cid
     * @return \Hussainweb\DrupalApi\Entity\Collection\EntityCollection|\Hussainweb\DrupalApi\Entity\Entity
     */
    public function getComment($cid);
}