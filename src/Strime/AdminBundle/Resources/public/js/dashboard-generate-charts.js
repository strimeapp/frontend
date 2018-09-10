$(document).ready(function() {

    // Generate the chart with the number of users per day
    var chartNbUsersPerDayData = generateChartDataNbUsersPerDay();

    var chartNbUsersPerDay = AmCharts.makeChart("chart-nb-users-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbUsersPerDayData,
        "valueAxes": [{
            "id":"nbUsers",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbUsers",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbUsers",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new users",
            "valueField": "nbUsers",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbUsers",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of users",
            "valueField": "totalNbUsers",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbUsersPerDay.addListener("dataUpdated", zoomChartNbUsers);
    zoomChartNbUsers();

    function zoomChartNbUsers(){
        chartNbUsersPerDay.zoomToIndexes(chartNbUsersPerDay.dataProvider.length - 20, chartNbUsersPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the percentage of active users per day
    var chartPercentageActiveUsersPerDayData = generateChartDataPercentageActiveUsersPerDay();

    var chartPercentageActiveUsersPerDay = AmCharts.makeChart("chart-percentage-active-users-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartPercentageActiveUsersPerDayData,
        "valueAxes": [{
            "id":"percentageActiveUsers",
            "axisColor": "#0FD8C1",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }],
        "graphs": [{
            "valueAxis": "percentageActiveUsers",
            "lineColor": "#0FD8C1",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Percentage of active users",
            "valueField": "percentageActiveUsers",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartPercentageActiveUsersPerDay.addListener("dataUpdated", zoomChartPercentageActiveUsers);
    zoomChartPercentageActiveUsers();

    function zoomChartPercentageActiveUsers(){
        chartPercentageActiveUsersPerDay.zoomToIndexes(chartPercentageActiveUsersPerDay.dataProvider.length - 20, chartPercentageActiveUsersPerDay.dataProvider.length - 1);
    }


    // Generate the donut with the number of users per offer
    var chartNbUsersPerOfferData = generateChartDataNbUsersPerOffer();

    var chartNbUsersPerOffer = AmCharts.makeChart( "chart-nb-users-per-offer-inner", {
        "type": "pie",
        "theme": "light",
        "titles": [ {
            "text": "Number of users per offer",
            "size": 16
        } ],
        "dataProvider": chartNbUsersPerOfferData,
        "valueField": "nb_users",
        "titleField": "offer",
        "startEffect": "elastic",
        "startDuration": 2,
        "labelRadius": 15,
        "innerRadius": "50%",
        "depth3D": 10,
        "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "angle": 15,
        "export": {
            "enabled": true
        }
    } );
    $( '.chart-input' ).off().on( 'input change', function() {
        var property = jQuery( this ).data( 'property' );
        var target = chartNbUsersPerOffer;
        var value = Number( this.value );
        chartNbUsersPerOffer.startDuration = 0;

        if ( property == 'innerRadius' ) {
            value += "%";
        }

        target[ property ] = value;
        chartNbUsersPerOffer.validateNow();
    } );


    // Generate the donut with the number of users per offer
    var chartNbUsersPerLocaleData = generateChartDataNbUsersPerLocale();

    var chartNbUsersPerLocale = AmCharts.makeChart( "chart-nb-users-per-locale-inner", {
        "type": "pie",
        "theme": "light",
        "titles": [ {
            "text": "Number of users per locale",
            "size": 16
        } ],
        "dataProvider": chartNbUsersPerLocaleData,
        "valueField": "nb_users",
        "titleField": "locale",
        "startEffect": "elastic",
        "startDuration": 2,
        "labelRadius": 15,
        "innerRadius": "50%",
        "depth3D": 10,
        "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "angle": 15,
        "export": {
            "enabled": true
        }
    } );
    $( '.chart-input' ).off().on( 'input change', function() {
        var property = jQuery( this ).data( 'property' );
        var target = chartNbUsersPerLocale;
        var value = Number( this.value );
        chartNbUsersPerLocale.startDuration = 0;

        if ( property == 'innerRadius' ) {
            value += "%";
        }

        target[ property ] = value;
        chartNbUsersPerLocale.validateNow();
    } );




    // Generate the chart with the number of projects per day
    var chartNbProjectsPerDayData = generateChartDataNbProjectsPerDay();

    var chartNbProjectsPerDay = AmCharts.makeChart("chart-nb-projects-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbProjectsPerDayData,
        "valueAxes": [{
            "id":"nbProjects",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbProjects",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbProjects",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new projects",
            "valueField": "nbProjects",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbProjects",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of projects",
            "valueField": "totalNbProjects",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbProjectsPerDay.addListener("dataUpdated", zoomChartNbProjects);
    zoomChartNbProjects();

    function zoomChartNbProjects(){
        chartNbProjectsPerDay.zoomToIndexes(chartNbProjectsPerDay.dataProvider.length - 20, chartNbProjectsPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of projects per day
    var chartNbVideosPerDayData = generateChartDataNbVideosPerDay();

    var chartNbVideosPerDay = AmCharts.makeChart("chart-nb-videos-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbVideosPerDayData,
        "valueAxes": [{
            "id":"nbVideos",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbVideos",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbVideos",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new videos",
            "valueField": "nbVideos",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbVideos",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of videos",
            "valueField": "totalNbVideos",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbVideosPerDay.addListener("dataUpdated", zoomChartNbVideos);
    zoomChartNbVideos();

    function zoomChartNbVideos(){
        chartNbVideosPerDay.zoomToIndexes(chartNbVideosPerDay.dataProvider.length - 20, chartNbVideosPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of images per day
    var chartNbImagesPerDayData = generateChartDataNbImagesPerDay();

    var chartNbImagesPerDay = AmCharts.makeChart("chart-nb-images-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbImagesPerDayData,
        "valueAxes": [{
            "id":"nbImages",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbImages",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbImages",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new images",
            "valueField": "nbImages",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbImages",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of images",
            "valueField": "totalNbImages",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbImagesPerDay.addListener("dataUpdated", zoomChartNbImages);
    zoomChartNbImages();

    function zoomChartNbImages(){
        chartNbImagesPerDay.zoomToIndexes(chartNbImagesPerDay.dataProvider.length - 20, chartNbImagesPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of audio files per day
    var chartNbAudiosPerDayData = generateChartDataNbAudiosPerDay();

    var chartNbAudiosPerDay = AmCharts.makeChart("chart-nb-audios-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbAudiosPerDayData,
        "valueAxes": [{
            "id":"nbAudios",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbAudios",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbAudios",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new audio files",
            "valueField": "nbAudios",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbAudios",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of audio files",
            "valueField": "totalNbAudios",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbAudiosPerDay.addListener("dataUpdated", zoomChartNbAudios);
    zoomChartNbAudios();

    function zoomChartNbAudios(){
        chartNbAudiosPerDay.zoomToIndexes(chartNbAudiosPerDay.dataProvider.length - 20, chartNbAudiosPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of comments posted on videos per day
    var chartNbCommentsPerDayData = generateChartDataNbCommentsPerDay();

    var chartNbCommentsPerDay = AmCharts.makeChart("chart-nb-comments-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbCommentsPerDayData,
        "valueAxes": [{
            "id":"nbComments",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbComments",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbComments",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new comments",
            "valueField": "nbComments",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbComments",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of comments",
            "valueField": "totalNbComments",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbCommentsPerDay.addListener("dataUpdated", zoomChartNbComments);
    zoomChartNbComments();

    function zoomChartNbComments(){
        chartNbCommentsPerDay.zoomToIndexes(chartNbCommentsPerDay.dataProvider.length - 20, chartNbCommentsPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of comments posted on images per day
    var chartNbImageCommentsPerDayData = generateChartDataNbImageCommentsPerDay();

    var chartNbImageCommentsPerDay = AmCharts.makeChart("chart-nb-image-comments-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbImageCommentsPerDayData,
        "valueAxes": [{
            "id":"nbComments",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbComments",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbComments",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new comments",
            "valueField": "nbComments",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbComments",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of comments",
            "valueField": "totalNbComments",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbImageCommentsPerDay.addListener("dataUpdated", zoomChartNbImageComments);
    zoomChartNbImageComments();

    function zoomChartNbImageComments(){
        chartNbImageCommentsPerDay.zoomToIndexes(chartNbImageCommentsPerDay.dataProvider.length - 20, chartNbImageCommentsPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of comments posted on audio files per day
    var chartNbAudioCommentsPerDayData = generateChartDataNbAudioCommentsPerDay();

    var chartNbAudioCommentsPerDay = AmCharts.makeChart("chart-nb-audio-comments-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbAudioCommentsPerDayData,
        "valueAxes": [{
            "id":"nbComments",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbComments",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbComments",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new comments",
            "valueField": "nbComments",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbComments",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of comments",
            "valueField": "totalNbComments",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbAudioCommentsPerDay.addListener("dataUpdated", zoomChartNbAudioComments);
    zoomChartNbAudioComments();

    function zoomChartNbAudioComments(){
        chartNbAudioCommentsPerDay.zoomToIndexes(chartNbAudioCommentsPerDay.dataProvider.length - 20, chartNbAudioCommentsPerDay.dataProvider.length - 1);
    }




    // Generate the chart with the number of projects per day
    var chartNbContactsPerDayData = generateChartDataNbContactsPerDay();

    var chartNbContactsPerDay = AmCharts.makeChart("chart-nb-contacts-per-day-inner", {
        "type": "serial",
        "theme": "light",
        "pathToImages": "../../../bundles/strimeadmin/js/amcharts_3.20.3.free/amcharts/images/",
        "legend": {
            "useGraphSettings": true
        },
        "dataProvider": chartNbContactsPerDayData,
        "valueAxes": [{
            "id":"nbContacts",
            "axisColor": "#FF6600",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "left"
        }, {
            "id":"totalNbContacts",
            "axisColor": "#FCD202",
            "axisThickness": 2,
            "gridAlpha": 0,
            "axisAlpha": 1,
            "position": "right"
        }],
        "graphs": [{
            "valueAxis": "nbContacts",
            "lineColor": "#FF6600",
            "bullet": "round",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Number of new contacts",
            "valueField": "nbContacts",
            "fillAlphas": 0
        }, {
            "valueAxis": "totalNbContacts",
            "lineColor": "#FCD202",
            "bullet": "square",
            "bulletBorderThickness": 1,
            "hideBulletsCount": 30,
            "title": "Total number of contacts",
            "valueField": "totalNbContacts",
            "fillAlphas": 0
        }],
        "chartScrollbar": {},
        "chartCursor": {
            "cursorPosition": "mouse"
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": true,
            "axisColor": "#DADADA",
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true,
            "position": "bottom-right"
        }
    });

    chartNbContactsPerDay.addListener("dataUpdated", zoomChartNbContacts);
    zoomChartNbContacts();

    function zoomChartNbContacts(){
        chartNbContactsPerDay.zoomToIndexes(chartNbContactsPerDay.dataProvider.length - 20, chartNbContactsPerDay.dataProvider.length - 1);
    }

});
