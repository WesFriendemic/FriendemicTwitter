;(function($) {
    var templates = {};
    var activeQuery = '';

    var initTemplates = function() {
        templates.tweetTemplate = _.template($('#tweetTemplate').html());
        templates.queryTemplate = _.template($('#queryTemplate').html());
        templates.loadingTemplate = _.template($('#loadingTemplate').html());
        templates.searchError = _.template($('#searchError').html());
    };

    var queryClick = function(e) {
        e.preventDefault();
        var query = $(this).data('query');
        var li = $(this).closest('li');
        var nav = $(this).closest('ul');

        nav.find('li').removeClass('active');
        li.addClass('active');

        performSearch(query, function(data) {
            $('#query').val(query);
            insertTweets(data.tweets);
        });
    };

    var performSearch = function(query, cb, ctx) {
        var url = 'index.php';
        var params = {
            controller: 'Api',
            action: 'GetTweets',
            query: query,
            tz_offset: (new Date()).getTimezoneOffset()/60
        };

        $('#content').html(templates.loadingTemplate());
        $('.graph-sidebar').addClass('hide-right');
        $('#greeting').remove();

        $.getJSON(url, params, function(data) {
            if(data.error) {
                console.log(data.error);
                $('#content').html(templates.searchError());
                return;
            }


            $('.graph-sidebar').removeClass('hide-right');
            TweetChart.setData(data.distribution.bins, data.distribution.subBins);

            cb.call(ctx || this, data);
        }).error(function() {
            console.log('Error while looking for tweets.');
            $('#content').html(templates.searchError());
        });
    };

    var insertTweets = function(tweets) {
        var container = $('#content');
        container.html('');

        var html = '';

        _.each(tweets, function(tweet) {
            html += templates.tweetTemplate({tweet: tweet, relativeFormat: window.relativeFormat});
        });

        container.html(html);
    };

    var getQueries = function(cb, ctx) {
        var url = 'index.php';
        var params = {
            controller: 'Api',
            action: 'GetQueries',
        };

        $.getJSON(url, params, function(data) {
            if(data.error) {
                console.log(data.error);
                return;
            }

            cb.call(ctx || this, data);
        });
    };

    var insertQueries = function(queries) {
        var container = $('#query_sidebar');
        container.html(templates.queryTemplate({queries: queries, activeQuery: activeQuery}));
    };

    var attachEvents = function() {
        $('body').on('click', '.query-link', queryClick);
        $('#search_form').on('submit', searchSubmit);
    };

    var searchSubmit = function(e) {
        e.preventDefault();
        var query = $('#query').val();

        performSearch(query, function(data) {
            insertTweets(data.tweets);
            getQueries(function(data) {
                insertQueries(data.queries);
            });
        });
    };

    var onLoad = function() {
        initTemplates();
        attachEvents();

        // This is just ... a hideous, ridiculous hack. Can't display:none the graph
        // when we shouldn't display it, because Highcharts calculates the width
        // of the chart based on the parent element.
        //
        // We want it the same width as the sidebar, so ... here, we literally
        // do that.
        //
        //If I didn't need to finish this today, I'd make this less hideous.
        $('.graph-sidebar').css('width', $('#query_sidebar').css('width'));

        window.TweetChart.initChart('#graph-container');
        $('[data-toggle=offcanvas]').click(function() {
            $('.row-offcanvas').toggleClass('active');
        });
    };

    $(onLoad);
}(jQuery));
