<?php

namespace App\Handler;

use Doctrine\Persistence\ManagerRegistry;

class NodeHandler
{
    const TYPE_PUBLIC_SERVICE = 'PublicService';
    const TYPE_ROUTE = 'Route';

    const REL_TYPE_NEED_OF = 'NEED_OF';

    /**
     * {@inheritdoc}
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine) {
        $this->doctrine = $doctrine;
    }

    public function getNode($identifier, string $type)
    {
        $connection = $this->getConnection();

        $stmString = "
            SELECT * FROM cypher('graph_name',
            $$ MATCH (v:%s %s) RETURN v $$ ) as (v agtype);
        ";

        $stmString = sprintf(
            $stmString,
            $type,
            sprintf('{identifier: \'%s\'}', $identifier),
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return null;
        }

        $string = str_replace('::vertex', '', $result[0]['v']);
        $json = json_decode($string);

        return $json;
    }

    public function getNodesBy($identifier, string $type)
    {
        return [];

        $connection = $this->getConnection();

        $stmString = "
            SELECT * FROM cypher('graph_name',
            $$ MATCH (v:%s %s) RETURN v $$ ) as (v agtype);
        ";

        $stmString = sprintf(
            $stmString,
            $type,
            sprintf('{identifier: \'%s\'}', $identifier),
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return null;
        }

        $string = str_replace('::vertex', '', $result[0]['v']);
        $json = json_decode($string);

        return $json;
    }

    public function getDependency($parentIdentifier, $parentType, $dependecyIndetifier, $dependecyType)
    {
        return [];

        $connection = $this->getConnection();

        $stmString = "SELECT * FROM 
            cypher('graph_name', $$
                MATCH
                    (x:{$parentType} %s)-[r]-(y:{$dependecyType} %s)
                RETURN as (r agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{identifier: \'%s\'}', $parentIdentifier),
            sprintf('{identifier: \'%s\'}', $dependecyIndetifier)
        );

        $result = $connection->fetchAllAssociative($stmString);

        dump($result);
        die;

        if (count($result) < 1) {
            return null;
        }

        $string = str_replace('::vertex', '', $result[0]['v']);
        $json = json_decode($string);

        return $json;
    }

    public function addNode($identifier, string $type, $data = null)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM cypher('graph_name', $$ CREATE  p = (:%s %s) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            $type,
            sprintf('{identifier: \'%s\'}', $identifier),
        );

        $connection->executeStatement($stmString);
    }

    public function addDependency($parentIdentifier, $parentType, $dependecyIndetifier, $dependecyType)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM 
            cypher('graph_name', $$
                MATCH
                    (x:{$parentType} %s),
                    (y:{$dependecyType} %s)
                CREATE p = (x)-[:NEED_OF]->(y) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{identifier: \'%s\'}', $parentIdentifier),
            sprintf('{identifier: \'%s\'}', $dependecyIndetifier)
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
