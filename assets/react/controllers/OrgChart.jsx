import React, { useLayoutEffect, useRef, useEffect } from 'react';
import { OrgChart } from 'd3-org-chart';

export const OrgChartComponent = (props, ref) => {
  const d3Container = useRef(null);
  let chart = null;

  function addNode(node) {
    chart.addNode(node);
  }

  if (props.setClick) {
    props.setClick(addNode);
  }

  // We need to manipulate DOM
  useLayoutEffect(() => {
    if (props.data && d3Container.current) {
      if (!chart) {
        chart = new OrgChart();
      }
      chart
        .container(d3Container.current)
        .data(props.data)
        .childrenMargin((d) => 90)
        .compactMarginBetween((d) => 65)
        .compactMarginPair((d) => 100)
        .neightbourMargin((a, b) => 50)
        .siblingsMargin((d) => 100)
        .nodeWidth((d) => 200)
        .nodeHeight((d) => 120)
        // .onNodeClick((d, i, arr) => {
        //   props.onNodeClick(d);
        // })
        .nodeContent((node) => {
          let link = ``;
          let button = ``;

          /**
           * This is hardcoded, but I don't know a way to access the router from here yet.
           * 
           */
          if (node.parent) {
            link = `${window.location.origin}/routes/${node.data.route.id}/items/${node.data.properties.identifier}/public_service`;
            button = `${window.location.origin}/routes/${node.data.route.id}/items/${node.data.properties.identifier}/public_service/delete/${node.data.edgeToParent}`;
          }

          if (node.data.isRoot) {
            link = `${window.location.origin}/routes/${node.data.route.id}/items`;
          }

          let title = ``;
          if (node.parent) {
            title = node.data.publicService.name;
          } else {
            title = node.data.route.name;
          }

          let buttons = ``;

          if (!node.data.isRoot) {
            buttons = `
              <a class="p-2 bg-white rounded-sm" href="${link}">Agregar</a>
              <form action="${button}"
                method="post"
                class="inline ml-2"
              >
                <button type="submit" class="underline pr-2 text-red-600 bg-red-200 p-2 rounded-sm">
                  Eliminar
                </a>
              </form>
            `;
          } else {
            buttons = `
              <a class="p-2 bg-white rounded-sm" href="${link}">Agregar</a>
            `;
          }

          return (`
            <div class="bg-gray-200 p-4" style="height: ${node.height}px; width: ${node.width}px;">
              <h6> ${title} </h6>
              ${buttons}
            </div>
          `);
        })
        .render()
        .expandAll();
    }
  }, [props.data, d3Container.current]);

  return (
    <div>
      <div ref={d3Container} />
    </div>
  );
};
