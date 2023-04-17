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
            SELECT * FROM cypher('graph_public_services',
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
        $connection = $this->getConnection();

        $stmString = "
            SELECT * from cypher('graph_public_services', $$
                MATCH (V:%s %s)-[R:NEED_OF *]->(V2)
                RETURN V,R,V2
            $$) as (V agtype, R agtype, V2 agtype);
        ";

        $stmString = sprintf(
            $stmString,
            $type,
            sprintf('{identifier: \'%s\'}', $identifier),
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return [];
        }

        $data = [];

        foreach ($result as $row => $item) {
            $data[$row] = [];

            foreach ($item as $idx => $value) {
                $string = str_replace('::vertex', '', $value);
                $string = str_replace('::edge', '', $string);

                $data[$row][$idx] = json_decode($string);
            }
        }

        return $data;
    }

    public function getDependency($parentIdentifier, $parentType, $dependecyIndetifier, $dependecyType, $routeId = null)
    {
        return [];

        $connection = $this->getConnection();

        $stmString = "SELECT * FROM 
            cypher('graph_public_services', $$
                MATCH
                    (x:{$parentType} %s)-[r:NEED_OF {%s}]-(y:{$dependecyType} %s)
                RETURN as (r agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{identifier: \'%s\'}', $parentIdentifier),
            $routeId ? sprintf('{routeId: \'%s\'}', $routeId) : '',
            sprintf('{identifier: \'%s\'}', $dependecyIndetifier)
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return null;
        }

        $string = str_replace('::vertex', '', $result[0]['r']);
        $json = json_decode($string);

        return $json;
    }

    public function addNode(string $identifier, string $type, $data = null)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM cypher('graph_public_services', $$ CREATE  p = (:%s %s) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            $type,
            sprintf('{identifier: \'%s\'}', $identifier),
        );

        $connection->executeStatement($stmString);
    }

    public function addDependency(string $parentIdentifier, $parentType, string $dependecyIndetifier, $dependecyType, $routeId)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * FROM 
            cypher('graph_public_services', $$
                MATCH
                    (x:{$parentType} %s),
                    (y:{$dependecyType} %s)
                CREATE p = (x)-[:NEED_OF %s]->(y) $$ ) as (p agtype);";

        $stmString = sprintf(
            $stmString,
            sprintf('{identifier: \'%s\'}', $parentIdentifier),
            sprintf('{identifier: \'%s\'}', $dependecyIndetifier),
            $routeId ? sprintf('{routeId: \'%s\'}', $routeId) : '',
        );

        $stm = $connection->executeStatement($stmString);

        // $result = $connection->fetchAllAssociative('SELECT * FROM cypher(\'graph_public_services\', $$ MATCH (v) RETURN v $$) as (v agtype);');
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

    public function removeDependencyById($dependecyId)
    {
        $connection = $this->getConnection();

        $stmString = "SELECT * from cypher('graph_public_services', $$
            MATCH (V)-[R:NEED_OF]->(V2)
            WHERE id(R) = %s
            DELETE R
            $$) as (R agtype);"
        ;

        $stmString = sprintf(
            $stmString,
            $dependecyId
        );

        $connection->executeStatement($stmString);
    }

    public function getPathByRouteAndDependency($routeIdentifier, $dependecyIndetifier)
    {
        $connection = $this->getConnection();

        $stmString = "
            SELECT * from cypher('graph_public_services', $$
                MATCH p = (V:Route { identifier: '%s' })-[R:NEED_OF *]->(V2 { identifier: '%s' })
                RETURN relationships(p)
            $$) as (V2 agtype);
        ";

        $stmString = sprintf(
            $stmString,
            $routeIdentifier,
            $dependecyIndetifier
        );

        $result = $connection->fetchAllAssociative($stmString);

        if (count($result) < 1) {
            return [];
        }

        $data = [];

        foreach ($result as $row => $item) {
            $data[$row] = [];

            foreach ($item as $idx => $value) {
                $string = str_replace('::vertex', '', $value);
                $string = str_replace('::edge', '', $string);

                $data[$row][$idx] = json_decode($string);
            }
        }

        return $data;
    }
}
