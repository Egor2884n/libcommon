<?php
//
// Generate message catalogues
//
require_once(__DIR__."/mcutils.php");

function file_encode($f) {
  if (file_exists($f)) {
    $itxt = file_get_contents($f);
    $otxt = html_entity_decode($itxt,ENT_NOQUOTES|ENT_HTML5,"UTF-8");
    if ($itxt != $otxt) {
      file_put_contents($f,$otxt);
      return TRUE;
    }
  }
  return FALSE;
}

function xgettext_r($po,$dirs) {
  foreach ($dirs as $srcdir) {
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir)) as $s){
      if (!is_file($s)) continue;
      if (!preg_match('/\.php$/',$s)) continue;
      $cmd = 'xgettext --no-wrap -o '.$po;

      if (file_exists($po)) {
	$cmd .= ' -j';
	$new = false;
      } else {
	$new = true;
      }
      $cmd .= ' '.$s;
      //echo ($cmd."\n");
      system($cmd);
      // Make sure the CHARSET is defined properly...
      if ($new && file_exists($po)) {
	$potxt = file_get_contents($po);
	if (preg_match('/Content-Type:\s+text\/plain;\s+charset=CHARSET/',$potxt)) {
	  file_put_contents($po,preg_replace('/\s+charset=CHARSET/',' charset=utf-8', $potxt));
	}
	unset($potxt);
      }
    }
  }
}

function mcgen($mcdir,array $dirs) {
  if (!is_dir($mcdir)) return;

  foreach ($dirs as $srcdir) {
    if (!is_dir($srcdir)) die("$srcdir: Source not found\n");
  }

  $templ = "$mcdir/messages.ini";
  if (!file_exists($templ)) file_put_contents($templ,"");

  foreach (glob("$mcdir/*.ini") as $mc) {
    $po = preg_replace('/\.ini$/','.po',$mc);

    if (file_exists($po)) unlink($po);
    xgettext_r($po,$dirs);
    if (!file_exists($po)) {
      echo ("xgettext_r error\n");
      return;
    }

    $nmsgs = mcutils::po_get(file_get_contents($po));
    // Add meta data tags...
    foreach (["lang","version"] as $tt) {
      $nmsgs["mc.".$tt] = "";
    }
    unlink($po);
    if ($nmsgs === null) {
      echo("Error reading $po\n");
      continue;
    }
    $in_ini = file_get_contents($mc);
    $omsgs = mcutils::ini_get($in_ini);
    if ($omsgs !== null) {
      // merge old messages -- tagging un-used translations
      foreach ($omsgs as $k=>$v) {
	if (substr($k,0,1) == "#" && !isset($nmsgs[$k])) $k = substr($k,1);
	if (isset($nmsgs[$k]))
	  $nmsgs[$k] = $v;
	else
	  $nmsgs["#$k"] = $v;
      }
    }
    $out_ini = "; ".basename($mc)."\n".mcutils::ini_set($nmsgs);

    if ($in_ini != $out_ini) {
      file_put_contents($mc,$out_ini);
      echo "Updated ".basename($mc)."\n";
    }
  }
}
