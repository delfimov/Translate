<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Translate</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
    </style>
</head>

<body>

<?php
include('lib/translate.php');

$t = new translate();
$t->fallback = 'ru';

?>


    <div class="container">

      <div class="hero-unit">
        <h1>Translate</h1>
        <p><?=$t->t('PHP library for loading translation files and generating translated strings.')?></p>
        <p><a href="https://github.com/Groozly/Translate" class="btn btn-primary btn-large"><?=$t->t('Translate on GitHub &raquo;')?></a></p>
      </div>

      <div class="row">
        <div class="span6">
          <h2><?=$t->t('Class synopsis')?></h2>
		  <p><?=$t->t('Documentation')?></p>
        </div>
        <div class="span6">
          <h2><?=$t->t('Usage example')?></h2>
			<pre class="prettyprint">
<?php
$s = '
include("lib/translate.php");

$t = new translate();
echo $t->t("some string");
echo $t->t("%s is %d", "key", 3);


$t = new translate("translate", "ru", "en", array("ru", "en"));
$l = rand(0, 100);
$t->choice("%d liter|%d liters", $l);
$t->choice("%s1", $l, null, "ru");
';

echo str_replace(
				'%s1', 
				'%d литр|%d литра|%d литров',
				htmlentities($s)
				);
?>
			</pre>
        </div>
      </div>

	  <hr>
	  
      <footer>
        <p>&copy; <a href="mailto:dmitry@elfimov.ru">Dmitry Elfimov</a> 2011&mdash;<?=date('Y')?></p>
      </footer>

    </div>

  </body>
</html>
