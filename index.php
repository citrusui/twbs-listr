<?php
error_reporting(1);
$sort        = array(
array(
'key' => 'lname',
'sort' => 'asc'
),
array(
'key' => 'size',
'sort' => 'asc'
)
);
$this_script = basename(__FILE__);
$this_folder = str_replace('/' . $this_script, '', $_SERVER['SCRIPT_NAME']);
$this_domain = $_SERVER['SERVER_NAME'];
$dir_name    = explode("/", $this_folder);
$file_list   = array();
$folder_list = array();
$total_size  = 0;
if ($handle = opendir('.')) {
while (false !== ($file = readdir($handle))) {
if ($file != "." && $file != ".." && $file != $this_script && !in_array($file, $ignore_list)) {
$stat          = stat($file);
$info          = pathinfo($file);
$item['name']  = $info['filename'];
$item['lname'] = strtolower($info['filename']);
$item['ext']   = $info['extension'];
$item['lext']  = strtolower($info['extension']);
$item['bytes'] = $stat['size'];
$item['size']  = bytes_to_string($stat['size'], 2);
$item['mtime'] = $stat['mtime'];
if ($info['extension'] != '') {
array_push($file_list, $item);
} else {
array_push($folder_list, $item);
}
clearstatcache();
}
}
closedir($handle);
}
if ($folder_list)
$folder_list = php_multisort($folder_list, $sort);
if ($file_list)
$file_list = php_multisort($file_list, $sort);
function php_multisort($data, $keys)
{
foreach ($data as $key => $row) {
foreach ($keys as $k) {
$cols[$k['key']][$key] = $row[$k['key']];
}
}
$idkeys = array_keys($data);
$i      = 0;
foreach ($keys as $k) {
if ($i > 0) {
$sort .= ',';
}
$sort .= '$cols[' . $k['key'] . ']';
if ($k['sort']) {
$sort .= ',SORT_' . strtoupper($k['sort']);
}
if ($k['type']) {
$sort .= ',SORT_' . strtoupper($k['type']);
}
$i++;
}
$sort .= ',$idkeys';
$sort = 'array_multisort(' . $sort . ');';
eval($sort);
foreach ($idkeys as $idkey) {
$result[$idkey] = $data[$idkey];
}
return $result;
}
function bytes_to_string($size, $precision = 0)
{
$sizes = array(
' YB',
' ZB',
' EB',
' PB',
' TB',
' GB',
' MB',
' KB',
' bytes'
);
$total = count($sizes);
while ($total-- && $size > 1024)
$size /= 1024;
$return['num'] = round($size, $precision);
$return['str'] = $sizes[$total];
return $return;
}
function time_ago($timestamp, $recursive = 0)
{
$current_time = time();
$difference   = $current_time - $timestamp;
$periods      = array(
"second",
"minute",
"hour",
"day",
"week",
"month",
"year",
"decade"
);
$lengths      = array(
1,
60,
3600,
86400,
604800,
2630880,
31570560,
315705600
);
for ($val = sizeof($lengths) - 1; ($val >= 0) && (($number = $difference / $lengths[$val]) <= 1); $val--);
if ($val < 0)
$val = 0;
$new_time = $current_time - ($difference % $lengths[$val]);
$number   = floor($number);
if ($number != 1) {
$periods[$val] .= "s";
}
$text = sprintf("%d %s ", $number, $periods[$val]);
if (($recursive == 1) && ($val >= 1) && (($current_time - $new_time) > 0)) {
$text .= time_ago($new_time);
}
return $text;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  </head>
  <title>Index of 
    <?
$parent = '/';
?>
    <?
foreach ($dir_name as $dir => $name):
?>
    <?
if (($name != ' ') && ($name != '') && ($name != '.')):
?>
    <?
$parent .= $name . '/';
?>
    <?
endif;
?>
    <?
endforeach;
?>
    <?= $parent; ?>
  </title>
  <body>
    <div class="container-fluid">
      <h1>
        <a href="https://<?= $this_domain ?>">
        <?= $this_domain ?>
        </a>
      <?
foreach ($dir_name as $dir => $name):
?>
      <?
if (($name != ' ') && ($name != '') && ($name != '.') && ($name != '/')):
?>
      <?
$parent = '';
?>
      <?
for ($i = 1; $i <= $dir; $i++):
?>
      <?
$parent .= $dir_name[$i] . '/';
?>
      <?
endfor;

?> / 
			<a href=/<?= $parent ?>>
			<?= $name ?>
			</a>
		<?
endif;
?>
    <?
endforeach;
?>
    </h1>
  </div>
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Name
        </th>
        <th>Size
        </th>
        <th>Date modified
        </th>
      </tr>
    </thead>
    <tbody>
      <?
if ($folder_list):
?>
      <?
foreach ($folder_list as $item):
?>
      <tr>
        <th>
          <a href=
             <?= $item['name'] ?>/>
          <?= $item['name'] ?>
    </a>
    </th>
  <th>
    <?= $item['size']['num'] ?>
    <?= $item['size']['str'] ?>
  </th>
  <th>
    <?= time_ago($item['mtime']) ?> ago
  </th>
  </tr>
<?
endforeach;
?>
<?
endif;
?>
<?
if ($file_list):
?>
<?
foreach ($file_list as $item):
?>
<tr>
  <th>
    <a href="<?= $item['name'] ?>.<?= $item['ext'] ?>">
    <?= $item['name'] ?>.<?= $item['ext'] ?>
</a>
</th>
<th>
  <?= $item['size']['num'] ?>
  <?= $item['size']['str'] ?>
</th>
<th>
  <?= time_ago($item['mtime']) ?> ago
</th>
</tr>
<?
endforeach;
?>
<?
endif;
?>
</table>
</div>
</body>
</html>
