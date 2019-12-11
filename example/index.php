<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<script src="http://code.jquery.com/jquery-latest.min.js"></script>

	<link rel="stylesheet" type="text/css" href="/style.css">
	<script type="text/javascript" src="/script.js" ></script>

	<title>EGH Demo</title>

</head>
<body>
<textarea style="width: 800px; height: 250px;" id="ajaxOutput" readonly="readonly" title="?"></textarea>

<br>

<a href="javascript:startAjaxUpdate()">[UPDATE]</a>
<br />
<br />
<br />
<br />


<script type="text/javascript">
	setInterval(refreshStatus, 500);

	let currentStatus = "N/A";

	function refreshStatus()
	{
		const ajaxOutput = $('#ajaxOutput');

		jQuery.ajax({
			url:    'ajaxStatus.php',
			success: function(result)
			{
				const newval = result + '\r\n.';
				if (newval === currentStatus) return;

				ajaxOutput.val(currentStatus = newval);
				ajaxOutput.scrollTop(ajaxOutput[0].scrollHeight);
			},
			error: function( jqXHR, textStatus, errorThrown)
			{
				const newval = 'AN ERROR OCCURED:' + '\r\n' + textStatus + '\r\n' + errorThrown;
				if (newval === currentStatus) return;

				ajaxOutput.val(currentStatus = newval);
				ajaxOutput.scrollTop(ajaxOutput[0].scrollHeight);
			},
			async:   true
		});

	}

	function startAjaxUpdate()
    {

		jQuery.ajax({
			url:    'ajaxUpdate.php',
			success: function(result)
			{
				console.log("Started [ajaxUpdate]");
			},
			error: function( jqXHR, textStatus, errorThrown) { alert(textStatus+"\r\n"+errorThrown); } ,
			async:   true
		});
    }
</script>

</body>
</html>