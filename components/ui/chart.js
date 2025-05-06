"use client"

import Chart from "chart.js/auto"

export { Chart }

export const ChartContainer = ({ children }) => {
  return <div className="chart-container">{children}</div>
}

export const ChartTooltip = ({ children }) => {
  return <div className="chart-tooltip">{children}</div>
}

export const ChartTooltipContent = ({ children }) => {
  return <div className="chart-tooltip-content">{children}</div>
}

export const ChartLegend = ({ children }) => {
  return <div className="chart-legend">{children}</div>
}

export const ChartLegendContent = ({ children }) => {
  return <div className="chart-legend-content">{children}</div>
}

export const ChartStyle = () => {
  return (
    <style jsx>{`
      .chart-container {
        position: relative;
        margin-bottom: 1rem;
      }

      .chart-tooltip {
        position: absolute;
        background: white;
        border: 1px solid #ccc;
        padding: 0.5rem;
        border-radius: 0.25rem;
        z-index: 10;
        pointer-events: none;
      }
    `}</style>
  )
}
