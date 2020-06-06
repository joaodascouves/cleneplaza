<?php

    static $page_info = Array(
        'title' => 'Store',
        'priority' => -1,
        'permission' => Array('admin', 'mod', 'user')

    );

    if( context_parse(__FILE__) )
        return $page_info;

    echo make_page(Array(
        'body.inner' => 'teste'
    ));