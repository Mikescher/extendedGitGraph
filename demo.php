<?php

include 'extendedGitGraph.php';

$v = new ExtendedGitGraph('Mikescher');

$v->addSecondaryUsername("Sam-Development");

$v->addSecondaryRepository("Anastron/ColorRunner");

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

		<script type="text/javascript" language="JavaScript">
			function startAjaxRefresh()
			{
				$('#ajaxOutput').val("");

				val = setInterval(
					function()
					{
						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result)
							{
								$('#ajaxOutput').val(result);
								$('#ajaxOutput').scrollTop($('#ajaxOutput')[0].scrollHeight);
							},
							async:   false
						});
					}, 500);

				jQuery.ajax({
					url:    'ajaxReload.php',
					success: function(result)
					{
						clearInterval(val);

						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result)
							{
								$('#ajaxOutput').val(result + '\r\n.');
								$('#ajaxOutput').scrollTop($('#ajaxOutput')[0].scrollHeight);
							},
							async:   true
						});
					},
					error: function( jqXHR, textStatus, errorThrown)
					{
						clearInterval(val);

						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result)
							{
								$('#ajaxOutput').val(result + '\r\n' + 'AN ERROR OCCURED:' + '\r\n' + textStatus);
								$('#ajaxOutput').scrollTop($('#ajaxOutput')[0].scrollHeight);
							},
							async:   true
						});
					},
					async:   true
				});

			}
		</script>

	</head>
	<body>
		<?php echo $v->loadFinishedContent(); ?>

		<textarea style="width: 800px; height: 250px;" id="ajaxOutput" readonly="readonly"></textarea>

		<br>

		<a href="javascript:startAjaxRefresh()">[REFRESH]</a>


		<?php

		if ( isset($_GET['collect']) && $_GET['collect'] === 'true')
		{
			$v->setToken(file_get_contents('api_token.secret'));
			$v->collect();

			$v->generateAndSave();
		}

		?>

	</body>
</html>