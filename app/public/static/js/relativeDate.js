;(function(root) {
    var parseDate = function(date) {
        var timestamp;

        if(!date) return null;

        if(!(date instanceof Date)) {
            timestamp = Date.parse(date);
            if(isNaN(timestamp)) return null;

            date = new Date(timestamp);
        }

        return date;

    };

    var months = [
        'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
    ];

    var formatSeconds = function(s, date) {
        return '' + Math.round(s) + 's';
    };

    var formatMinutes = function(s, date) {
        return '' + Math.round(s/60) + 'm';
    };

    var formatHours = function(s, date) {
        return '' + Math.round(s/3600) + 'h';
    };

    var formatDefault = function(s, date) {
        var month = date.getMonth();
        var day = date.getDate();
        return '' + day + ' ' + months[month];
    }

    var intervals = {
        59: formatSeconds,
        3600: formatMinutes,
        86400: formatHours,
    }

    var relativeFormat = function(date) {
        if(!date) return 'now';

        var now = new Date();
        date = parseDate(date);

        if(date === null) return 'now';

        var interval = (now - date) / 1000;

        for(var key in intervals) {
            if(!intervals.hasOwnProperty(key)) continue;

            if(interval < key) {
                return intervals[key](interval, date);
            }
        }

        return formatDefault(interval, date);
    };

    root.relativeFormat = relativeFormat;
}(window));
