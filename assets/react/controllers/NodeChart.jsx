import React, { useState, useEffect } from 'react';
import { OrgChartComponent } from './OrgChart';
import * as d3 from 'd3';


const data = [
    {
        nodeId: 'parent',
        parentNodeId: null,
    },
    {
        nodeId: 'child-1',
        parentNodeId: 'parent',
    },
    {
        nodeId: 'child-2',
        parentNodeId: 'parent',
    }
];

export default function NodeChart(props) {
    let nodesData = [];

    if (props.nodes.length >= 1) {
        // Here we add the root node. We assume that is the first node of the first edge
        nodesData.push({
            nodeId: props.nodes[0].v.id,
            route: props.route,
            isRoot: true,
            parentNodeId: null
        });

        // Here we get the last node of each edge, assume that the other side of the edge is already in the nodes list
        props.nodes.forEach((item) => {
            nodesData.push({
                nodeId: item.v2.id,
                properties: item.v2.properties,
                publicService: props.publicServiceData[item.v2.properties.identifier],
                route: props.route,
                parentNodeId: item.r[item.r.length - 1].start_id,
                edgeToParent: item.r[item.r.length - 1].id
            });
        });
    } else {
        nodesData.push({
            nodeId: 'root',
            route: props.route,
            isRoot: true,
            parentNodeId: null
        });
    }

    return (
        <OrgChartComponent
            data={nodesData}
        />
    );
}