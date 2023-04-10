<?php

namespace App\Handler;

use App\Entity\PublicService as EntityPublicService;
use Doctrine\Persistence\ManagerRegistry;

class PublicService
{
    /**
     * {@inheritdoc}
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine) {
        $this->doctrine = $doctrine;
    }

    public function processRowsAndCreate($data)
    {
    }

    public function getNode(EntityPublicService $node)
    {
        $connection = $this->getConnection();

        $stmString = "
            SELECT * FROM cypher('graph_name',
            $$ MATCH (v:Tramite %s) RETURN v $$ ) as (v agtype);
        ";

        $stmString = sprintf(
            $stmString,
            sprintf('{publicServiceId: \'%s\'}', $node->getId()),
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return null;
        }

        $string = str_replace('::vertex', '', $result[0]['v']);
        $json = json_decode($string);

        return $json;
    }

    public function addNode(EntityPublicService $node)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM cypher('graph_name', $$ CREATE  p = (:Tramite %s) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{publicServiceId: \'%s\'}', $node->getId()),
        );

        $connection->executeStatement($stmString);
    }

    public function addDependency(EntityPublicService $parent, EntityPublicService $dependecy)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM cypher('graph_name', $$ MATCH (x:Tramite %s), (y:Tramite %s) CREATE  p = (x)-[:NEED_OF]->(y) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{publicServiceId: \'%s\'}', $parent->getId()),
            sprintf('{publicServiceId: \'%s\'}', $dependecy->getId())
        );

        $stm = $connection->executeStatement($stmString);

        // $result = $connection->fetchAllAssociative('SELECT * FROM cypher(\'graph_name\', $$ MATCH (v) RETURN v $$) as (v agtype);');
        // $string = str_replace('::vertex', '', $result[0]['v']);
        // $json = json_decode($string);
    }

    /**
     * {@inheritdoc}
     */
    private function getConnection()
    {
        /**
         * @var Connection
         */
        $connection = $this->doctrine->getConnection('graph');

        $connection->executeQuery('LOAD \'age\';');
        $connection->executeQuery('SET search_path = ag_catalog, "$user", public;');

        return $connection;
    }
}
