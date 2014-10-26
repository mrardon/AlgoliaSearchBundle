<?php
namespace Algolia\AlgoliaSearchSymfonyDoctrineBundle\Tests\Indexer;

class Indexer extends \Algolia\AlgoliaSearchSymfonyDoctrineBundle\Indexer\Indexer
{
    public $creations = array();
    public $updates = array();
    public $deletions = array();
    public $isolated_from_algolia;

    public function isolateFromAlgolia($isolated_from_algolia = true)
    {
        $this->isolated_from_algolia = $isolated_from_algolia;

        return $this;
    }

    public function performBatchCreations(array $creations)
    {
        $this->creations = $creations;

        if (!$this->isolated_from_algolia) {
            return parent::performBatchCreations($creations);
        }
    }

    public function performBatchUpdates(array $updates)
    {
        $this->updates = $updates;

        if (!$this->isolated_from_algolia) {
            return parent::performBatchUpdates($updates);
        }
    }

    public function performBatchDeletions(array $deletions)
    {
        $this->deletions = $deletions;
        if (!$this->isolated_from_algolia) {
            return parent::performBatchDeletions($deletions);
        }
    }

    public function reset()
    {
        $this->creations = array();
        $this->updates = array();
        $this->deletions = array();
    }

    protected function newInstance()
    {
        $indexer = parent::newInstance();
        $indexer->isolateFromAlgolia($this->isolated_from_algolia);

        return $indexer;
    }

    public function setApiSettings($application_id, $api_key)
    {
        global $kernel;

        try {
            if ($kernel->getContainer()->getParameter('algolia.get_secrets_from_travis')) {
                $application_id = getenv('ALGOLIA_APPLICATION_ID');
                $api_key = getenv('ALGOLIA_API_KEY');
            }
            
        } catch (\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException $e) {
            // ignore this, it means we're just not running on Travis
        }

        parent::setApiSettings($application_id, $api_key);

        return $this;
    }

    public function getAlgoliaIndexName($entity_or_class)
    {
        // add a second layer of "environment" when running on Travis
        // so that concurrent tests don't step on each other's toes trying to query the same index
        return metaenv(parent::getAlgoliaIndexName($entity_or_class));
    }

    public function makeEnvIndexName($indexName, $perEnvironment)
    {
        return metaenv(parent::makeEnvIndexName($indexName, $perEnvironment));
    }
}
