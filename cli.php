<?php
/**
 * Typecho to Canvas
 * Easily convent your data from Typecho to Canvas.
 *
 * @author kirainmoe[i@kirainmoe.com]
 * @version 0.1
 * @link https://github.com/kirainmoe
 */

// environment check
if (php_sapi_name() != 'cli') {
  echo 'Converter can only run in php-cli mode.';
  exit;
}

// import dependencies
try {
  include dirname(__FILE__) . '/lib/medoo.php';
  include dirname(__FILE__) . '/lib/Parsedown.php';
} catch(Exception $e) {
  echo 'Converter could not find core dependencies.';
  exit;
}

$typecho = array();
$canvas  = array();
$parsedown = new Parsedown();

echo "\n\033[31mWelcome to [Typecho to Canvas]!\033[0m\n
This is a conventer that helps you easily convent your data from Typecho to Canvas blog platform.\n
\033[35mNote: you must have Canvas blog platform installed so that you can finish converting process.\033[0m\n\n\n";

sleep(1);

echo "\033[33m[1/4] Collecting database config for Typecho:\033[0m\n\n";

/**
 * Step 1/4
 * Collect databse config for Typecho
 */
echo "Typecho database host (127.0.0.1): ";
$server = trim(fgets(STDIN));
$typecho['server'] = empty($server) ? '127.0.0.1' : $server;

echo "Typecho database user (root): ";
$username = trim(fgets(STDIN));
$typecho['username'] = empty($username) ? 'root' : $username;

echo "Typecho database password: ";
$typecho['password'] = trim(fgets(STDIN));

echo "Typecho database name (typecho): ";
$dbname = trim(fgets(STDIN));
$typecho['database_name'] = empty($dbname) ? 'typecho' : $dbname;

echo "Typecho database port (3306): ";
$port = trim(fgets(STDIN));
$typecho['port'] = empty($port) ? 3306 : $port;

echo "Typecho database type [mysql, sqlite, postgresql]: ";
$type = trim(fgets(STDIN));
$typecho['database_type'] = empty($type) ? 'mysql' : $type;

$typecho['charset'] = 'utf8';

sleep(1);

/**
 * Step 2/4
 * Collect database config for Canvas
 */
echo "\n\n\033[33m[2/4] Collecting database config for Canvas:\033[0m\n\n";

while (true) {
  echo "Do you have Typecho and Canvas installed at the same database server? (y/n)";
  $isAtTheSameDb = strtolower(trim(fgets(STDIN)));

  if ($isAtTheSameDb == 'y') {
    $canvas = $typecho;

    echo "Canvas database name (canvas): ";
    $dbname = trim(fgets(STDIN));
    $canvas['database_name'] = empty($dbname) ? 'canvas' : $dbname;

    break;
  } else if ($isAtTheSameDb == 'n') {
    echo "Canvas database host (127.0.0.1): ";
    $server = trim(fgets(STDIN));
    $canvas['server'] = empty($server) ? '127.0.0.1' : $server;

    echo "Canvas database user (root): ";
    $username = trim(fgets(STDIN));
    $canvas['username'] = empty($username) ? 'root' : $username;

    echo "Canvas database password: ";
    $canvas['password'] = trim(fgets(STDIN));

    echo "Canvas database name (canvas): ";
    $dbname = trim(fgets(STDIN));
    $canvas['database_name'] = empty($dbname) ? 'canvas' : $dbname;

    echo "Canvas database port (3306): ";
    $port = trim(fgets(STDIN));
    $canvas['port'] = empty($port) ? 3306 : $port;

    echo "Canvas database type [mysql, sqlite, postgresql]: ";
    $type = trim(fgets(STDIN));
    $canvas['database_type'] = empty($type) ? 'mysql' : $type;

    $canvas['charset'] = 'utf8';
    break;
  } else {
    echo "Input is illegal.\n";
  }
}

echo "\nInformation collecting completed. Please check these information: \n";
echo "Typecho database config: ";
print_r($typecho);
echo "\nCanvas database config: ";
print_r($canvas);

echo "\nIs this OK? (y/n)";
$isThisOK = strtolower(trim(fgets(STDIN)));
if ($isThisOK != 'y') {
  echo '  Oops! Please restart the converter and repeat the operation above.';
  exit;
}

sleep(1);

echo "\n\033[33m[3/4] Copying data... \033[0m\n\n";
echo "  Please wait since this process can take a while.\n\n";

try {
  $te = new medoo($typecho);
  $cn = new medoo($canvas);
} catch (Exception $e) {
  echo "\033[31m  Oops! Can not connect to database. Failed to execute operation. Aborting...\033[0m";
  exit;
}

echo "\033[35m  Converting metas(tags)...\033[0m\n    ";
$rawTags = $te->select('typecho_metas', '*', array('type' => 'tag'));
$teTags = array();
$cnTags = array();
foreach ($rawTags as $val)
{
  $teTags[$val['mid']] = $val['slug'];
  $cnTagId = $cn->insert('canvas_tags', array(
    'tag' => $val['name'],
    'title' => $val['name'],
    'subtitle' => $val['slug'],
    'meta_description' => empty($val['description']) ? '' : $val['description'],
    'layout' => 'canvas::frontend.blog.index',
    'reverse_direction' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
  ));
  $cnTags[$val['slug']] = $cnTagId;
  echo '.';
}
echo "done!\n";

echo "\033[35m  Copying posts...\033[0m\n    ";
$rawPosts = $te->select('typecho_contents', '*', array(
  'type' => array('post', 'page')
));
$cnPostsMap = array();

foreach ($rawPosts as $val)
{
  $parsedContent = $parsedown->text(str_replace('<!--markdown-->', '', $val['text']));
  $cnPostId = $cn->insert('canvas_posts', array(
    'user_id' => 1,     // webmaster for default author
    'slug' => $val['slug'],
    'title' => $val['title'],
    'subtitle' => mb_substr(trim(strip_tags($parsedContent)), 0, 100),
    'content_raw' => str_replace('<!--markdown-->', '', $val['text']),
    'content_html' => $parsedContent,
    'page_image' => '',
    'meta_description' => '',
    'is_draft' => ($val['status'] == 'publish' ? 0 : 1),
    'layout' => 'canvas::frontend.blog.post',
    'created_at' => date('Y-m-d H:i:s', $val['created']),
    'updated_at' => date('Y-m-d H:i:s', $val['modified']),
    'published_at' => date('Y-m-d H:i:s', $val['created'])
  ));

  $cnPostsMap[$val['cid']] = $cnPostId;
  echo '.';
}
echo "done!\n";

echo "\033[35m  Mapping posts to tags...\033[0m\n    ";
foreach ($cnPostsMap as $key => $value)
{
  $metas = $te->select('typecho_relationships', '*', array('cid' => $key));

  foreach ($metas as $val)
  {
    if (isset($teTags[$val['mid']])) {
      $cn->insert('canvas_post_tag', array(
        'post_id' => $value,
        'tag_id' => $cnTags[$teTags[$val['mid']]],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
      ));
    }

    echo '.';
  }
}
echo "done!\n";

echo "\033[35m  Synchronizing settings...\033[0m\n";

sleep(1);

echo "\n\033[33m[4/4] Congratulations! \033[0m\n\n";
echo "You have successfully converted from Typecho to Canvas! Enjoy~\n";
echo "If you have any further issues, please refer to https://github.com/kirainmoe/typecho-to-canvas.\n";
echo "Copyright(c) 2017 kirainmoe | https://kirainmoe.com";
