<?php

  include 'comment.control.php';
  include 'services/credit.php';

  /**
  *  Alias for context_entry_by_id (includes/controls/core.php).
  *
  *  @param Integer id
  *  @return Array
  */
  function mirror_get_by_id($id)
  {
    return context_entry_by_id('mirrors', $id, Array(
      Array('INET_NTOA(`ip`)', 'ip'),
      'url',
      'fullpage_path',
      'created_at'
    ));
  }

  /**
  *  Alias for collection_fetch (includes/controls/core.php).
  *
  *  @param Array $parameters
  *  @return Array
  */
  function mirror_collection_fetch($parameters)
  {
    $collection = collection_fetch($parameters, 'mirrors', Array(
      Array('`c`.`url`', 'label'),
      Array('`c`.`preview_path`', 'file_path'),
      Array('`c`.`views`', 'stats')
    ));

    foreach( $collection['wall'] as &$item )
    {
      $comments = comment_get_count_by_id('mirror', $item['ID']);
      $item['stats'] = sprintf("(V:%d|R:%d|I:%d)", $item['stats'], $comments[0], $comments[1]);
    }

    return $collection;
  }

  /**
  *  @param String $url
  *  @return Array
  */
  function mirror_url_filter($url)
  {
    if( @empty($url) )
      return Array(
        'status' => 1,
        'message'=> 'URL is not present.'
      );

    if( !filter_var($url, FILTER_VALIDATE_URL, FILTER_SCHEME_REQUIRED) )
      return Array(
        'status' => 2,
        'message'=> 'Malformed URL.'
      );
  }

  /**
  *  Fetchs an URL then check if specified code is present.
  *
  *  @param Array $parameters
  *  @return Array
  */
  function mirror_check_and_insert($parameters)
  {
    // $config['timeout']
    global $config;
    global $conn;

    $error = 1;

    if( @is_array($url_error = @mirror_url_filter(($url = trim($parameters['url']))) ) )
      return Array(
        'status' => 50 + $url_error['status'],
        'message'=> $url_error['message']
      );

    $flags = Array();
    $parsed_url = parse_url($url);

    $url_domain = $parsed_url['host'];
    $url_scheme = $parsed_url['scheme'];

    if( @empty($parsed_url['path']) || !@strcmp('/', $parsed_url['path']) )
    {
      // URL does not have a path, so flag homepage is added.
      array_push($flags, 'homepage');
    }

    if( !@strcmp(($url_ip = gethostbyname($url_domain)), $url_domain) )
      return Array(
        'status' => $error++,
        'message'=> 'Coudn\'t determine domain IP address.'
      );

    // POSIX time tolerance.
    $tolerance = time() - $config['mirror_unique_expiry'];

    $query = mysqli_query($conn, sprintf("SELECT UNIX_TIMESTAMP(`created_at`) AS `created_at`, UNIX_TIMESTAMP(`updated_at`) AS `updated_at`
      FROM `cl_mirrors` WHERE `domain`='%s' ORDER BY `updated_at` DESC, `created_at` DESC",

      $url_domain)
    );

    if( @mysqli_num_rows($query)>0 )
    {
      if( ($result = mysqli_fetch_assoc($query)) )
      {
        if( $result['created_at']>$tolerance || $result['updated_at']>$tolerance )
        {
          return Array(
            'status' => $error++,
            'message'=> 'Mirror was already notified in the past 365 days.'
          );
        }

        // URL with the same domain was already notified, so reincident
        // flag is added.
        array_push($flags, 'reincident');
      }
    }

    // Time until mirrors with same IP address are considered mass submition.
    $tolerance = time() - 60*60*24*3;

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_mirrors` WHERE `ip`=%d
      AND (UNIX_TIMESTAMP(`updated_at`)>%d OR UNIX_TIMESTAMP(`created_at`)>%d)",

      ip2long($url_ip),
      $tolerance,
      $tolerance
    ));

    if( @mysqli_num_rows($query)>0 )
      array_push($flags, 'mass');

    // Installing curl for sending HTTP request with proper headers
    // is highly advisable.
    if( function_exists('curl_init') )
    {
      $curl = curl_init();

      if( !$curl )
        return Array(
          'status' => $error++,
          'message'=> 'Curl failed to initialize.'
        );

      curl_setopt_array($curl, Array(
        CURLOPT_URL => $url,
        // Some old websites displays an error message if browser isn't IE.
        CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
        CURLOPT_TIMEOUT => $config['http_timeout'],
        CURLOPT_ENCODING => '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true

      ));

      // Triple equals are necessary when comparing to zero.
      if( strpos($url, 'https://') === 0)
      {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      }

      if( !@empty($page_body = curl_exec($curl)) )
      {
        if( curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200 )
          return Array(
            'status' => $error++,
            'message'=> 'Request returned a HTTP code different from 200.'
          );

        if( !@strstr('text/html', curl_getinfo($curl, CURLINFO_CONTENT_TYPE)) )
          return Array(
            'status' => $error++,
            'message'=> 'Response type is not text/html.'
          );
      }
      else
      {
        return Array(
          'status' => $error++,
          'message'=> 'HTTP request failed.'
        );
      }
    }
    else
    {
      if( function_exists('file_get_contents') )
      $page_body = file_get_contents($url, $context = stream_context_create(Array(
        'http' => Array(
          'method' => 'GET',
          'header' => 'User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        )
      )));
    }

    if( @empty($page_body) )
      return Array(
        'status' => $error++,
        'message'=> 'Request returned an empty body.'
      );

    if( @strstr($page_body, $config['mirror_validity_code']) )
    {
      $unix_time = round(microtime(true)*1000);
      $suffix = substr(md5($url), 0, 5);

      $mirror_fullpage = sprintf('%smirrors/fullpage/%s-%s.html',
        $config['upload_path'],
        $unix_time,
        $suffix
      );

      $mirror_thumb = sprintf('%smirrors/preview/%s-%s.jpeg',
        $config['upload_path'],
        $unix_time,
        $suffix
      );

      file_put_contents($mirror_fullpage, $page_body);
      system("wkhtmltoimage --quality 30 --height 650 --crop-x 135 --crop-w 750 --crop-y 20 $mirror_fullpage $mirror_thumb &>/dev/null");


      $flags_str = '';
      foreach( $flags as $index => $flag )
      {
        $flags_str .= $flag;

        if( $index < sizeof($flags)-1 )
          $flags_str .= ',';
      }

      $query = mysqli_query($conn, sprintf("INSERT INTO `cl_mirrors` (`user_id`, `ip`, `url`, `domain`, `fullpage_path`, `preview_path`, `flags`)
        VALUES ('%d', '%d', '%s', '%s', '%s', '%s', '%s')",

        current_user_get()['ID'],
        ip2long($url_ip),
        $url,
        $url_domain,
        $mirror_fullpage,
        $mirror_thumb,
        $flags_str
      ));

      if( !$query )
        return Array(
          'status' => $error++,
          'message'=> 'Mirror could\'t be inserted in database.'
        );

      $mirror_id = mysqli_insert_id($conn);

      $mirror_config = include 'services/mirror.php';
      $amount = calculate_credit($mirror_config);

      if( ($result = credit_current_user($amount, $mirror_id, 'mirror', 'mirror submitted'))['status'] )
        return $result;

      return Array(
        'status' => 0,
        'message'=> 'Mirror submited successfully.'
      );
    }
    else
    {
      return Array(
        'status' => $error++,
        'message'=> 'Mirror validity code is not present in response body.'
      );
    }
  }
