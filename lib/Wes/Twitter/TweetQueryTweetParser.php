<?php
namespace Wes\Twitter;

use Wes\Model\ParserBase;

class TweetQueryTweetParser extends ParserBase {
    /*
     * 'virtual' means don't touch that field, just insert/update it.
     * Pretty much used for fields that come from a different source
     * than the incoming json
     */
    protected static $parseFields = array(
        'tweet_id' => 'id_str',
        'query' => array(
            'type' => 'virtual',
        ),
    );
}
