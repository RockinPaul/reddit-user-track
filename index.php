<!doctype html5>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel=stylesheet href=style.css />
<script src=remember.js></script>
<div id=links>
<?php

include('db.php');
$db = db();
$perPage = 30;

$use_fail = isset($_GET['fail']);
$last = $_GET['last'] ?? 4;
$newest = $_GET['newest'] ?? 0;
$format = $_GET['format'] ?? '*';
$max = $_GET['max'] ?? PHP_INT_MAX;
$min = $_GET['min'] ?? 0;
$page = $_GET['page'] ?? 0;
$userList = $_GET['users'] ?? null;

$start = $page * $perPage;
$end = ($page + 1) * $perPage;

function dolink($k, $v) {
  $copy = $_GET;
  $copy[$k] = $v;
  return "?" . http_build_query($copy);
}

if(!empty($userList)) {
  $last = 'all';
}


foreach([2,4,8,16,36,72,24*7,24*7*3,24*7*5] as $t) {
  if ($t > 48) {
    if ($t > 24 * 14) {
      $unit = $t / (24 * 7) . " week";
    } else {
      $unit = $t / 24 . " day";
    }
  } else {
    $unit = "$t hour";
  }
  
  $klass = ($last == $t) ? 'class=active' : '';
  echo "<a $klass href='" . dolink('last', $t) . "'>$unit</a>";
}
$klass = '';
if($last == 'all') { 
  $klass = 'class=active';
  if(empty($_GET['last'])) { 
    $last = 24 * 365 * 20;
  }
}
echo "<a $klass href='" . dolink('last', 'all') . "'>all</a>";
echo "</div>";
echo "<div id=content>";

$now = time();
$res = [];
$filter = false;

if($use_fail) {
  $filter = array_keys(json_decode(file_get_contents('fail.json'), true));
}

if(isset($userList)) {
  $toShow = array_map(function($res) { 
    return "data/" . $res;
  }, explode(',',$userList));
  var_dump($toShow, $userList);
} else {
  $toShow = glob("data/*");
}

$ix = 0;
foreach($toShow as $user) {
  $is_first = true;
  $user_short = basename($user);
  if($filter && !in_array($user_short, $filter)) {
    continue;
  }

  $row = [];
  $count = 0;
  $list = glob("$user/*.$format");
  usort($list, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
  if ($min > count($list) || count($list) > $max ) {
    continue;
  }
  foreach($list as $f) {

    $fname = basename($f);
    if ($fname == 'urllist.txt' || $fname == 'donelist.txt') {
      continue;
    }
    $row[] = $fname;
    
    $when = filemtime($f);
    if($last == 'all' || ($now - $when < 3600 * $last  && $now - $when > 3600 * $newest) || $filter) {
      if($is_first) {
        $ix ++;
        if($ix <= $start || $ix > $end) {
          // don't clear the is_first and we won't add it
          break;
        }
      }
      $count ++;
      if($count > 3) {
        continue;
      }
      if($is_first) {
        echo "<div data-last=" . floor(($now - $when) / 3600) . " data-user='$user_short' class='cont wrap'>";
        echo "<span class=user></span>";
        echo "<div class=inner>" ;
        $is_first = false;
      }
      $what = pathinfo($f);
      if($what['extension'] == 'mp4' || $what['extension'] == 'gifv') {
        echo "<video class=video autoplay loop muted='' nocontrols><source src='$f'></video>";
      } else {
        echo "<img title='$fname' data-src='$f'>";
      }
    }
  }
  if(!$is_first) { 
    $res[$user_short] = $row;
    echo "</div>";
    echo "</div>";
  }
}
echo "</div><div id=paging>";
if($page > 0) {
  echo "<a href=" . dolink('page', $page - 1) . ">prev</a>";
}
if($ix > $start + $perPage) {
  echo "<a href=" . dolink('page', $page + 1) . ">next</a>";
}
?> 
</div>
<script>
var all = <?=json_encode($res);?>, db = <?=json_encode($db) ?>;
</script>
