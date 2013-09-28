<?php
namespace Wes\Twitter;

use Wes\Model\ParserBase;

class TweetParser extends ParserBase {
    protected static $parseFields = array(
        'text',
        'id' => 'id_str',
        'created_at' => array(
            'field' => 'created_at',
            'type' => 'datetime',
        ),
        'retweeted',
        'retweet_count',
        'user_id' => array(
            'field' => array('user', 'id_str')
        ),
        'user_name' => array(
            'field' => array('user', 'name')
        ),
        'user_profile_image_url' => array(
            'field' => array('user', 'profile_image_url')
        ),
    );
}
