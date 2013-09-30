;(function(root) {
    var colors = Highcharts.getOptions().colors;
    var subCats = [];

    /*
     * This got a little involved. Just making categories for times
     * between 12 AM and 12 PM, and sub categories in ten minute intervals.
     */
    var categories = _.map(_.range(25), function(i) {
        var ampm = i >= 12 ? 'PM' : 'AM';
        i = i > 12 ? i-12 : i;
        if(i == 0) i = 12;

        var subCat = _.map(_.range(0, 60, 10), function(min) {
            var low = min;
            var high = i+9;
            var minute = min == 0 ? '00' : ''+min;
            return '' + i + ':' + minute;
        });
        subCats.push(subCat);

        return '' + i + ' ' + ampm;
    });

    var data;
    var chart;
    var name = "Tweets";

    /*
     * Transform the incoming bins and subBins into the format expected by
     * Highcharts
     */
    var setData = function(bins, subBins) {
        data = _.map(bins, function(datum, i) {
            return {
                y: datum,
                color: colors[i%2],
                drilldown: {
                    name: categories[i],
                    categories: subCats[i],
                    data: subBins[i],
                    color: colors[i%2]
                }
            }
        });
        setChart(name, categories, data);
    };

    /*
     * Draw / redraw the chart
     */
    var setChart = function(name, categories, data, color) {
        chart.xAxis[0].setCategories(categories, false);
        chart.series[0].remove(false);
        chart.addSeries({
            name: name,
            data: data,
            color: color || 'white'
        }, false);
        chart.redraw();
    };

    var initChart = function(selector) {
        var el = $(selector);
        chart = el.highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Tweet Distribution'
            },
            subtitle: {
                  text: 'Click a column to drill down'
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                title: {
                      text: 'Number of Tweets'
                }
            },
            plotOptions: {
                column: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                var drilldown = this.drilldown;
                                if(drilldown) {
                                    setChart(drilldown.name, drilldown.categories, drilldown.data, drilldown.color);
                                } else {
                                    setChart(name, categories, data);
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        color: colors[0],
                        style: {
                            fontWeight: 'bold'
                        },
                        formatter: function() {
                            return this.y;
                        }
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    var point = this.point,
                    s = this.x + ':<b>' + this.y + ' tweets</b><br />';
                    if(point.drilldown) {
                        s += 'Click to view ' + point.category;
                    } else {
                        s += 'Click to return to overview';
                    }

                    return s;
                }
            },
            series: [{
                name: name,
                data: data,
                color: 'white'
            }],
            exporting: {
                enabled: false
            }
        }).highcharts();
    };

    var TweetChart = {};
    TweetChart.setData = setData;
    TweetChart.initChart = initChart;
    root.TweetChart = TweetChart;
}(window));
