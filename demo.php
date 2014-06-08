<?php

include 'extendedGitGraph.php';

$v = new ExtendedGitGraph('Mikescher');

//$v->authenticate('???', '???', '???');

//$v->setToken('???');
//$v->collect();

$v->loadData();

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">

		<script src="http://code.jquery.com/jquery-latest.min.js"></script>

		<link rel="stylesheet" type="text/css" href="style.css">
		<script type="text/javascript" language="JavaScript">
			<?php include 'script.js'; ?>
		</script>
	</head>
	<body>
		<?php
			echo $v->generateAndSave();
			//echo $v->loadFinished();
		?>
	</body>
</html>