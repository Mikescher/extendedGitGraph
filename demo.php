<?php

include 'extendedGitGraph.php';

$v = new ExtendedGitGraph('Mikescher');

//$v->authenticate('7e26c5f1621349c14a7d');

//$v->setToken('7b3f6443cdd4b2f92d75c4c8aa83cfda6c7ca3ce');
//$v->collect();

$v->load();

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
			foreach($v->getYears() as $year) {
				echo '<div style="min-height: 170px; border: 1px solid black; margin: 10px; border-radius: 16px;">';
				echo $v->generate($year);
				echo '</div>';
			}
		?>



	</body>
</html>