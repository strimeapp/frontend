$(document).ready(function() {

    // Generate the chart with the number of users per day
    var chartStatsEncodingJobsTimeData = generateChartDataStatsEncodingJobsTime();

    var chartStatsEncodingJobsTime = AmCharts.makeChart("chart-stats-encoding-jobs-time-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartStatsEncodingJobsTimeData,
        "valueAxes": [{
            "id":"totalTime",
            "axisColor": "#0CAC9A",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"videoSize",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }, {
            "id":"videoDuration",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "totalTime",
            "lineColor": "#0CAC9A",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Time to encode the video (in seconds)",
            "valueField": "totalTime",
            "fillAlphas": 0
        }, {
            "valueAxis": "videoSize",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Size of the video (in octets)",
            "valueField": "videoSize",
            "fillAlphas": 0
        }, {
            "valueAxis": "videoDuration",
            "lineColor": "#FF6600",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Duration of the video (in seconds)",
            "valueField": "videoDuration",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "id",
        "categoryAxis": {
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartStatsEncodingJobsTime.addListener("dataUpdated", zoomChartStatsEncodingJobsTime);
    zoomChartStatsEncodingJobsTime();

    function zoomChartStatsEncodingJobsTime(){
        chartStatsEncodingJobsTime.zoomToIndexes(chartStatsEncodingJobsTime.dataProvider.length - 50, chartStatsEncodingJobsTime.dataProvider.length - 1);
    }

});
