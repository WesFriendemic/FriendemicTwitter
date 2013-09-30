;(function($) {
    var templates = {};
    var activeQuery = '';

    var initTemplates = function() {
        templates.tweetTemplate = _.template($('#tweetTemplate').html());
        templates.queryTemplate = _.template($('#queryTemplate').html());
        templates.loadingTemplate = _.template($('#loadingTemplate').html());
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
            console.log(data);
            insertTweets(data.tweets);
        });
    };

    var performSearch = function(query, cb, ctx) {
        var url = 'index.php';
        var params = {
            controller: 'Api',
            action: 'GetTweets',
            query: query
        };

        $('#content').html(templates.loadingTemplate());
        $('#graph-container').addClass('hidden');

        $.getJSON(url, params, function(data) {
            if(data.error) {
                console.log(data.error);
                return;
            }
            $('#graph-container').removeClass('hidden');
            TweetChart.setData(data.distribution.bins, data.distribution.subBins);

            cb.call(ctx || this, data);
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
        window.TweetChart.initChart('#graph-container');
    };

    $(onLoad);
}(jQuery));
