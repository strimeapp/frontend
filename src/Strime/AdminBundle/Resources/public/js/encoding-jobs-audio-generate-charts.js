$(document).ready(function() {

    // Generate the chart with the number of users per day
    var chartStatsAudioEncodingJobsTimeData = generateChartDataStatsAudioEncodingJobsTime();

    var chartStatsAudioEncodingJobsTime = AmCharts.makeChart("chart-stats-encoding-jobs-time-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartStatsAudioEncodingJobsTimeData,
        "valueAxes": [{
            "id":"totalTime",
            "axisColor": "#0CAC9A",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"audioSize",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }, {
            "id":"audioDuration",
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
            "title": "Time to encode the audio file (in seconds)",
            "valueField": "totalTime",
            "fillAlphas": 0
        }, {
            "valueAxis": "audioSize",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Size of the audio file (in octets)",
            "valueField": "audioSize",
            "fillAlphas": 0
        }, {
            "valueAxis": "audioDuration",
            "lineColor": "#FF6600",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Duration of the audio file (in seconds)",
            "valueField": "audioDuration",
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

    chartStatsAudioEncodingJobsTime.addListener("dataUpdated", zoomChartStatsAudioEncodingJobsTime);
    zoomChartStatsAudioEncodingJobsTime();

    function zoomChartStatsAudioEncodingJobsTime(){
        chartStatsAudioEncodingJobsTime.zoomToIndexes(chartStatsAudioEncodingJobsTime.dataProvider.length - 50, chartStatsAudioEncodingJobsTime.dataProvider.length - 1);
    }

});
