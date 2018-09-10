$(document).ready(function() {

    // Generate the donut with the number of users using Social Signin
    var chartDataNbUsersUsingSocialSignin = generateChartDataNbUsersUsingSocialSignin();

    var chartNbUsersUsingSocialSignin = AmCharts.makeChart( "chart-nb-users-using-social-signin", {
        "type": "pie",
        "theme": "light",
        "titles": [ {
            "text": "Ratio of users using Google or Facebook Signin",
            "size": 16
        } ],
        "dataProvider": chartDataNbUsersUsingSocialSignin,
        "valueField": "nb_users",
        "titleField": "social_signin",
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
        var target = chartNbUsersUsingSocialSignin;
        var value = Number( this.value );
        chartNbUsersUsingSocialSignin.startDuration = 0;

        if ( property == 'innerRadius' ) {
            value += "%";
        }

        target[ property ] = value;
        chartNbUsersUsingSocialSignin.validateNow();
    } );


    // Generate the donut with the number of users using Slack
    var chartDataNbUsersUsingSlack = generateChartDataNbUsersUsingSlack();

    var chartNbUsersUsingSlack = AmCharts.makeChart( "chart-nb-users-using-slack", {
        "type": "pie",
        "theme": "light",
        "titles": [ {
            "text": "Number of users using Slack",
            "size": 16
        } ],
        "dataProvider": chartDataNbUsersUsingSlack,
        "valueField": "nb_users",
        "titleField": "slack",
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
        var target = chartNbUsersUsingSlack;
        var value = Number( this.value );
        chartNbUsersUsingSlack.startDuration = 0;

        if ( property == 'innerRadius' ) {
            value += "%";
        }

        target[ property ] = value;
        chartNbUsersUsingSlack.validateNow();
    } );

});
