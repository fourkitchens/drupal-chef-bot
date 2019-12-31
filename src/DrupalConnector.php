<?php

namespace App;

use App\Contracts\DrupalConnectorInterface;
use Hussainweb\DrupalApi\Client;
use Hussainweb\DrupalApi\Request\CommentRequest;
use Hussainweb\DrupalApi\Request\NodeRequest;
use Hussainweb\DrupalApi\Request\Request;
use Hussainweb\DrupalApi\Request\TaxonomyTermRequest;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DrupalConnector implements LoggerAwareInterface, DrupalConnectorInterface
{
    /**
     * The d.o client.
     *
     * @var Client
     */
    protected $client;

    /**
     * A Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The issue tag cache.
     *
     * @var CacheInterface
     */
    protected $tagCache;

    /**
     * Constructs a DrupalConnector object.
     *
     * @param CacheInterface $issueTagsCache
     */
    public function __construct(CacheInterface $issueTagsCache)
    {
        Request::$userAgent = "DrupalChef Slack Bot";
        $this->client = new Client();
        $this->tagCache = $issueTagsCache;
    }

    /**
     * Gets a node from the d.o. api.
     *
     * @param string $nid
     *
     * @return bool|\Hussainweb\DrupalApi\Entity\Collection\EntityCollection|\Hussainweb\DrupalApi\Entity\Entity
     */
    public function getNode(string $nid) {
        $node = FALSE;
        try {
            $node = $this->client->getEntity(new NodeRequest($nid));
        }
        catch (\Throwable $e){
            $this->logger->error($e->getMessage());
        }
        return $node;
    }

    /**
     * Gets a comment from d.o.
     *
     * @param $cid
     * @return \Hussainweb\DrupalApi\Entity\Collection\EntityCollection|\Hussainweb\DrupalApi\Entity\Entity
     */
    public function getComment($cid) {
        return $this->client->getEntity(new CommentRequest($cid));
    }
    /**
     * Gets the name for an issue tag, from the cache if possible.
     *
     * @param string $tid
     *
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTag(string $tid): string {
        return $this->tagCache->get($tid, [$this, 'retrieveTag']);
    }

    /**
     * Retrieves an issue tag from D.o.
     *
     * @param CacheItemInterface $item
     * @return string
     */
    public function retrieveTag(CacheItemInterface $item): string {
        $tid = $item->getKey();
        $term = $this->client->getEntity(new TaxonomyTermRequest($tid));
        $item->set($term->name);
        return $term->name;

    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
