<?php

namespace SalesForceDataManager;

use League\Csv\Reader;

class DataManager
{
    public $client;

    public function __construct(
        \BulkApiClient $bulkapiClient
    ) {
        $this->client = $bulkapiClient;
    }


    public function query($soql, $sobject)
    {

        $job = new \JobInfo();
        $job->setObject($sobject);
        $job->setOpertion('query');
        $job->setContentType(\BulkApiClient::CSV);
        $job->setConcurrencyMode('Parallel');

        $job = $this->client->createJob($job);
        $batch = $this->client->createBatch($job, $soql);
        $this->client->updateJobState($job->getId(), 'Closed');


        $sleepTime = 7;
        $resultList = [];
        while($batch->getState() == 'Queued' || $batch->getState() == 'InProgress') {
            sleep($sleepTime *= 1.1);
            $batch = $this->client->getBatchInfo($job->getId(), $batch->getId());
        }

        try {
            $resultList = $this->client->getBatchResultList($job->getId(), $batch->getId());
        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(),"No result-list found") === false){
                throw $ex;
            } else {
                return new \ArrayIterator();
            }
        }
        foreach ($resultList as $resultId) {
            $result[] = $this->client->getBatchResult($job->getId(), $batch->getId(), $resultId);
        }
        $csvResult = Reader::createFromString($result[0]);
        $offset = 0;
        return $csvResult->fetchAssoc($offset);
    }




}
