<?php
// BLP 2021-03-09 -- NOTE .htaccess does not have the RewriteCond set at this time.
// BLP 2018-05-07 -- FOR ALL Natural computing company
// For this to run under 'apache' 
// we would need to edit our '.htaccess' file and add:
//   RewriteEngine on
//   RewriteCond %{REQUEST_FILENAME} !-f
//   RewriteCond %{REQUEST_FILENAME] !-d
//   RewriteRule . altoroute.php [L]
// Which means everything will go through 'altorouts.php' that is not a file or directory.
// 'composer require altorouter/altorouter'
// https://github.com/dannyvankooten/AltoRouter
// 'composer require pug-php/pug:^3.0'
// https://github.com/pug-php/pug

// This file DOES NOT DO a require_onece(getenv("SITELOADNAME") or set $S.
// The $router->map() functions can do it to set up $_site.

require_once("/var/www/vendor/autoload.php");
ErrorClass::setDevelopment(true);
ErrorClass::setErrorType(E_ALL & ~(E_NOTICE | E_WARNING | E_STRICT));
$router = new AltoRouter();

// getSiteLoad()
// for pug files we get the requestUri name, instanciate the $S
// and set $info. Return $info and $S

function getSiteLoad($name) {
  $__info->requestUri = $name;
  $_site = require_once(getenv("SITELOADNAME"));
  $S = new $_site->className($_site);

  if(!$S->isBot) {
    if(preg_match("~^.*(?:(msie\s*\d*)|(trident\/*\s*\d*)).*$~i", $S->agent, $m)) {
      $which = $m[1] ? $m[1] : $m[2];
      echo <<<EOF
<!DOCTYPE html>
<html>
<head>
  <title>NO GOOD MSIE</title>
</head>
<body>
<div style="background-color: red; color: white; padding: 10px;">
Your browser's <b>User Agent String</b> says it is:<br>
$m[0]<br>
Sorry you are using Microsoft's Broken Internet Explorer ($which).</div>
<div>
<p>You should upgrade to Windows 10 and Edge if you must use MS-Windows.</p>
<p>Better yet get <a href="https://www.google.com/chrome/"><b>Google Chrome</b></a>
or <a href="https://www.mozilla.org/en-US/firefox/"><b>Mozilla Firefox</b>.</p></a>
These two browsers will work with almost all previous
versions of Windows and are very up to date.</p>
<b>Better yet remove MS-Windows from your
system and install Linux instead.
Sorry but I just can not continue to support ancient versions of browsers.</b></p>
</div>
</body>
</html>
EOF;
      exit();
    }
  }

  // It might be better to get $top and $footer and put them into $info but that would take a lot
  // of rework on the code so instead we just parse a couple items and have the rest of the <head>
  // section hard coded into pug/layout.pug.
  
  $info = [
           'copyright'=>$S->copyright,
           'author'=>$S->author,
           'desc'=>'',
           'LAST_ID'=>$S->LAST_ID,
           '__Phone'=>'(505)399-9193',
           'keywords'=>"Window Cleaning, Carpet Cleaning, ".
                       "Janitorial Services, ".
                       "Chemical Free Cleaning, ".
                       "Toxin Free CleaningM, "
          ];
  return [(object)$info, $S];
};

function myGetLastMod($url) {
  $lastmod[0] = date("Y-m-d H:i T", filemtime($url));
  $lastmod[1] = date("Y-m-d H:i T", filemtime('pug/layout.pug'));
  return max($lastmod);
};
  
// Do Routing

$router->map('GET', '/', doindex);

$router->map('GET', '/index', doindex);

function doindex() {
  list($info, $S) = getSiteLoad('/index');
  date_default_timezone_set('America/New_York');
  $lastmod = myGetLastMod('pug/index.pug');
  //error_log("index latmod: $lastmod");
  $info->title = "All Natural Cleaning Company";
  $info->desc = "All Natural Cleaning Company. We clean with 100% toxin free products. Full service Commercial Janitorial and Home cleaning.";
  $pug = new Pug();
  $pug->displayFile('pug/index.pug', ['lastmod'=>$lastmod, 'info'=>$info]);
};

$router->map('GET', '/contact', function() {
  list($info, $S) = getSiteLoad('/contact');
  $lastmod = myGetLastMod('pug/contact.pug');
  $info->title = "Contact Us - All Natural Cleaning Company";
  $info->desc = "Contact Us at allnatural@allnaturalcleaningcompany.com. All Natural Cleaning Company. Toxin free Home and Commercial cleaning.";
  $info->value = $_GET['value'];
  $pug = new Pug();
  $pug->displayFile('pug/contact.pug', ['lastmod'=>$lastmod, 'info'=>$info]);
});

$router->map('POST', '/contactpost', function() {
  //vardump($_POST);
  list($info, $S) = getSiteLoad('/contactpost');
  $lastmod = myGetLastMod('pug/contactpost.pug');
  extract($_POST);
  $subject = $S->escape($subject);
  $message = $S->escape($message);

  $sql = "insert into contact_emails (site, email, name, subject, message, created) ".
         "values('$S->siteName', '$email', '$name', '$subject', '$message', now())";

  //echo "$sql<br>";
  
  $S->query($sql);
  
  $info->info = <<<EOF
site: $S->siteName
email: $email
name: $name
subject: $subject
message: $message
EOF;
  error_log("Email Address: $S->EMAILADDRESS");
  mail($S->EMAILADDRESS, "$subject", "$info->info", "From: allnatural@allnaturalcleaningcompany.com\r\n");
  mail('bartonphillips@gmail.com', "$subject", "$info->info", "From: allnatural@allnaturalcleaningcompany.com\r\n");
  // The Submit page

  $pug = new Pug();
  $pug->displayFile('pug/contactpost.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/residential', function() {
  list($info, $S) = getSiteLoad('/residential');
  $lastmod = myGetLastMod('pug/residential.pug');
  $info->title = "Home Cleaning Plans - All Natural Cleaning Company";
  $info->desc = "Home Cleaning with all natural products. Residential cleaning. No toxins or harmfull chemicals.";

  $pug = new Pug();
  $pug->displayFile('pug/residential.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/residentialbasic', function() {
  list($info, $S) = getSiteLoad('/residentialbasic');
  $lastmod = myGetLastMod('pug/residentialbasic.pug');
  $info->title = "Home Cleaning Basic Plan - All Natural Cleaning Company";
  $info->desc = "Our basic home cleaning plan. We use no toxic chemical, only natural products.";

  $pug = new Pug();
  $pug->displayFile('pug/residentialbasic.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/residentialextensive', function() {
  list($info, $S) = getSiteLoad('/residentialextensive');
  $lastmod = myGetLastMod('pug/residentialextensive.pug');
  $info->title = "Home Cleaning Extensive Plan - All Natural Cleaning Company";
  $info->desc = "Our Extensive home cleaning plan. We do everything that needs cleaning with all natural products. No toxic chemicals.";

  $pug = new Pug();
  $pug->displayFile('pug/residentialextensive.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/commercial', function($fname, $lname) {
  list($info, $S) = getSiteLoad('/commercial');
  $lastmod = myGetLastMod('pug/commercial.pug');
  $info->title = "Commercial Janitorial Service - All Natural Cleaning Company";
  $info->desc = "Comercial Janitorial service. All Natural products, no harmfull chemicals or toxins.";

  $pug = new Pug();
  $pug->displayFile('pug/commercial.pug', ['lastmod'=>$lastmod, 'info'=>$info]);
});

$router->map('GET', '/getquote', function() {
  list($info, $S) = getSiteLoad('/getquote');
  $lastmod = myGetLastMod('pug/getquote.pug');
  $info->title = "Get A Quote - All Natural Cleaning Company";
  $info->desc = "Get a Quote for Home or Commercial cleaning. Cheamical free cleaning. Residential and Commercial cleaning.";

  $pug = new Pug();
  $pug->displayFile('pug/getquote.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('POST', '/getquotepost', function() {
  list($info, $S) = getSiteLoad('/getquotepost');
  $lastmod = myGetLastMod('pug/getquotepost.pug');

  $keys = $values = '';
  
  foreach($_POST as $k=>$v) {
    $keys .= "$k,";
    $values .= "'". $S->escape($v) ."',";
    $info->info .= "$k: $v\n";
  }
  $keys = rtrim($keys, ',');
  $values = rtrim($values, ',');
  $sql = "insert into getquote ($keys) values($values)";

  $S->query($sql);
  mail($S->EMAILADDRESS, "Get Quote", $info->info, "From: info@allnaturalcleaningcompany.com");
  mail('bartonphillips@gmail.com', "Get Quote", $info->info, "From: info@allnaturalcleaningcompany.com");

  $pug = new Pug();
  $pug->displayFile('pug/getquotepost.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/about', function() {
  list($info, $S) = getSiteLoad('/about');
  $lastmod = myGetLastMod('pug/about.pug');
  $info->title = "About Us - All Natural Cleaning Company";
  $info->desc = "All Natural Cleaning Company. About Us. ".
                "Cleaning service for home and business. We use only all natural products, no toxic chemicals. ".
                "Our products are 99% edable.";

  $pug = new Pug();
  $pug->displayFile('pug/about.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/employment', function() {
  list($info, $S) = getSiteLoad('/employment');
  $lastmod = myGetLastMod('pug/employment.pug');
  $info->title = "$info->__City, $info->__State - All Natural Cleaning Company";
  $info->desc = "All Natural Cleaning Company. We clean with 100% toxin free products. Full service Commercial Janitorial and Home cleaning.";

  $pug = new Pug();
  $pug->displayFile('pug/employment.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('POST', '/employmentpost', function() {
  list($info, $S) = getSiteLoad('/employmentpost');
  $lastmod = myGetLastMod('pug/employmentpost.pug');
  extract($_POST);

  $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  
  $coinfo = $avfrom = $avtill = $r = $rphone = $dble = '';

  for($i=0; $i < 2; ++$i) {
    $coinfo .= "company: $company[$i], From: $from[$i], To: $to[$i]\n".
               "phone: $companyphone[$i], Job: $jobtitle[$i], Super: $supername[$i]\n";
  }
  
  foreach($av_from as $key=>$val) {
    $avfrom .= "$days[$key]: $val\n";
  }
  foreach($av_till as $key=>$val) {
    $avtill .= "$days[$key]: $val\n";
  }
  foreach($dbl as $key=>$val) {
    $dble .= "$days[$key]: $val\n";
  }
  
  foreach($ref as $key=>$val) {
    $r .= "ref$key: $val\n";
  }
  foreach($refphone as $key=>$val) {
    $rphone .= "refphone$key: $val\n";
  }
  $info->msg = <<<EOF
Full name: $name
Email: $email
Address: $address
Primary Phone: $phone
Legal: $legal
Can Start: $start

$coinfo
Available from:
$avfrom
Till
$avtill
Can Work Double
$dble
Reference
$r
Ref Phone
$rphone
EOF;
  mail($S->EMAILADDRESS, "Employment Request", $msg, "From: allnatural@allnaturalcleaningcompany.com");
  mail('bartonphillips@gmail.com', "Employment Request", $msg, "From: allnatural@allnaturalcleaningcompany.com");
  
  $pug = new Pug();
  $pug->displayFile("pug/employmentpost.pug", ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/recipes', function() {
  list($info, $S) = getSiteLoad('/recipies');
  $lastmod = myGetLastMod('pug/recipes.pug');
  
  $info->title = "Safe Cleanig Recipes - All Natural Cleaning Company";
  $info->desc = "Safe Cleaning Recipes. All Natural Cleaning. ".
                "Cleaning service for home and business. We use only all natural products, no toxic chemicals. ".
                "Our products are 99% edable.";
  $pug = new Pug();
  $pug->displayFile('pug/recipes.pug', ['info'=>$info, 'lastmod'=>$lastmod]);
});

$router->map('GET', '/webstats', 'getWebstats');

// **********************************
// Do the match

$match = $router->match();

// call closure or display 404 status

if($match && is_callable($match['target'])) {
  // This calls the closer with the params.
	call_user_func_array($match['target'], $match['params']); 
} else {
  // no route was matched
  $path = $_SERVER['REQUEST_URI'];
	header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
  echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<title>404 Not Found</title>
</head>
<body>
<h1>404 Not Found</h1>
<p>Sorry this path ($path) was not found on the server.</p>
</body>
</html>
EOF;
}

// Webstats

function getWebstats() {
  list($info, $S) = getSiteLoad('/webstats');
  $info->desc = "Web Starts for Allnatural";

  $S->query("select * from barton.tracker ".
            "where site='Allnatural' and lasttime > current_date() order by lasttime desc");

  $list = [];
  
  while($row = $S->fetchrow('assoc')) {
    $list[] = $row;
  }

  date_default_timezone_set('America/New_York');
  $lastmod = myGetLastMod('pug/webstats.pug');
  
  $pug = new Pug();
  $pug->displayFile('pug/webstats.pug', ['list'=>$list, 'lastmod'=>$lastmod, 'info'=>$info]);
};
