extendedGitGraph
================

Displays a Commit Table for every of your github-years.
This is practically a copy of githubs commitGraph functionality. 
But with the extra feature of showing commits older than a year.

*See it live in action [here](http://www.mikescher.de/about)*

![](https://raw.githubusercontent.com/Mikescher/extendedGitGraph/master/README-DATA/preview.png)

### How to use:

Create a new ExtendedGitGraph object

~~~php
include 'extendedGitGraph.php';

$v = new ExtendedGitGraph('Mikescher');
~~~

Optionaly you can add Secondary Usernames an Repositories that will also be included in your history.
This is needed because by default only your own repositories will be scanned

~~~php
$v->addSecondaryUsername("Sam-Development");
$v->addSecondaryRepository("Anastron/ColorRunner");
~~~

If thi is the first time you use the Github API you need to genrate a token:

~~~php
$v->authenticate('key', 'client id', 'client secret');
~~~

Otherwise you can set the token you got directly:

~~~php
$v->setToken('token');
~~~

Becauses the scanning takes a long time EGH caches the results. 
If you want to show the graph from cache call `loadFinishedContent()`. *(In this case you don't need to set the token)*

~~~php
echo $v->loadFinishedContent();
~~~

If you want to rescan everything call:

~~~php
$v->collect();
$v->generateAndSave();
~~~

### Reload with Ajax:

The reloading can take a **long**, **loooong** time if you have a lot of commits and repositories.
Because of that you can also refresh via Ajax:

 - Call the file `ajaxReload.php` to start the reloading
 - Call teh file `ajaxStatus.php` to get the current status (for displaying purposes)

**Attention:** Change the code in `ajaxReload.php` to fit your username / token etc

Below an example implementation with jQuerys Ajax calls:

~~~html

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">

		<script src="http://code.jquery.com/jquery-latest.min.js"></script>

		<script type="text/javascript" language="JavaScript">
			function startAjaxRefresh() {
				$('#ajaxOutput').val("");

				val = setInterval(
					function() 	{
						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result) {
								$('#ajaxOutput').val(result);
								$('#ajaxOutput').scrollTop($('#ajaxOutput')[0].scrollHeight);
							},
							async:   false
						});
					}, 500);

				jQuery.ajax({
					url:    'ajaxReload.php',
					success: function(result) {
						clearInterval(val);

						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result) {
								$('#ajaxOutput').val(result + '\r\n.');
								$('#ajaxOutput').scrollTop($('#ajaxOutput')[0].scrollHeight);
							},
							async:   true
						});
					},
					error: function( jqXHR, textStatus, errorThrown) {
						clearInterval(val);
						jQuery.ajax({
							url:    'ajaxStatus.php',
							success: function(result) {
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
		<textarea style="width: 800px; height: 250px;" id="ajaxOutput" readonly="readonly"></textarea>
		<a href="javascript:startAjaxRefresh()">[REFRESH]</a>
	</body>
</html>
~~~
