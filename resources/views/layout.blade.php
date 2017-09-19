<!DOCTYPE html>
<html>
<head>
	<title>NFC-E Data Extact</title>
	<link rel="icon" type="image/png" href="https://cdn1.iconfinder.com/data/icons/CrystalClear/22x22/actions/ark_extract.png">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<!-- Bootstrap core CSS -->
	<link href="https://getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom styles for this template -->
	<link href="https://getbootstrap.com/docs/4.0/examples/narrow-jumbotron/narrow-jumbotron.css" rel="stylesheet">

	@yield('script')
</head>
<body>

	<div class="container">
		<div class="header clearfix">
			<nav>
				<ul class="nav nav-pills float-right">
					<li class="nav-item">
						<a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/extractor">Extrator</a>
					</li>
				</ul>
			</nav>
			<h3 class="text-muted">Pay Less</h3>
		</div>

		@yield('content')

		<footer class="footer">
			<p>&copy; <a href="http://www.ufsm.br" target="_blank">UFSM</a> 2017</p>
		</footer>

	</div> <!-- /container -->
</body>
</html>