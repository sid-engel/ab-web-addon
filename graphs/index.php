<?php
require("../database.php");
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $lang['title']; ?> - <?php echo $lang['graphs']; ?></title>
		<link rel="shortcut icon" href="../data/img/icon.png" type="image/x-icon">
		<link rel="icon" href="../data/img/icon.png" type="image/x-icon">
		<link rel="stylesheet" href="../data/css/ab-web-addon.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/css/materialize.min.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	</head>
	<body>
		<div class="navbar-fixed">
		<nav class="grey darken-4">
		<div class="nav-wrapper container">
				<a href="/" class="brand-logo left"><?php echo $lang['title']; ?></a>
				<ul class="right hide-on-med-and-down">
						<li><a href="/"><?php echo $lang['punishments']; ?></a></li>
				    <li class="active"><a href="#"><?php echo $lang['graphs']; ?></a></li>
						<!-- Dropdown Trigger -->
						<li><a class="dropdown-button" href="#" data-activates="dropdown1" data-beloworigin="true">Credits<i class="material-icons right">arrow_drop_down</i></a></li>
				</ul>
		</div>
</nav>
<!-- Dropdown Structure -->
<ul id="dropdown1" class="dropdown-content collection">
			  <li><a href="https://github.com/mathhulk/ab-web-addon">Mathhulk</a></li>
				<li><a href="https://github.com/sid-engel">Sid Engel</a></li>
		    <li><a href="https://www.spigotmc.org/resources/advancedban.8695/">AdvancedBan</a></li>
</ul>
</div>
<div class="header container-fluid">
		<h1 style="margin: 0px;"><br><?php echo $lang['title']; ?></h1>
		<h5 style="padding-bottom: 2%;"><?php echo $lang['description']; ?></h5>
</div>
<div class="container" style="padding-top: 2%;">
				<p>
					<?php
					foreach($types as $type) {
						$result = mysqli_query($con, "SELECT * FROM `".$info['history']."` WHERE ".($info['compact'] == true ? "punishmentType LIKE '%".strtoupper($type)."%'" : "punishmentType='".strtoupper($type)."'"));
						if($type == 'all') {
							$result = mysqli_query($con, "SELECT * FROM `".$info['history']."`".($info['ip-bans'] == false ? " WHERE punishmentType!='IP_BAN'" : ""));
						}
						echo '<a href="/?type='.$type.'" class="waves-effect waves-light btn grey darken-4" style="margin: 3px;">'.strtoupper($lang[$type.($type != 'all' ? 's' : '')]).' <span class="">- '.mysqli_num_rows($result).'</span></a>';
					}
					?>
				</p>
</div>
<div class="container">
	<form method="get" action="user/">
		<div class="input-group">
			<input type="text" maxlength="50" name="user" class="form-control" placeholder="<?php echo $lang['search']; ?>">
			<span class="input-group-btn">
				<button class="btn btn-default grey darken-4" type="submit"><?php echo $lang['submit']; ?></button>
			</span>
		</div>
	</form>
</div>
			<div class="container" style="padding-bottom: 2%;">
				<div class="chart-container">
					<canvas id="chart"></canvas>
				</div>
			</div>
		</div>

		<script
			src="https://code.jquery.com/jquery-3.2.1.js"
			integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
			crossorigin="anonymous"></script>

		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.6.3/angular-animate.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-materialize/0.2.2/angular-materialize.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/js/materialize.min.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js"></script>
		<script>
		var myChart = new Chart($("#chart"), {
			type: "line",
			data: {
				<?php
				for($day = 6; $day >= 0; $day--) {
					$days[] = '"'.formatDate('l', strtotime("-".$day." days") * 1000).'"';
				}
				echo "
				labels: [".implode(", ", $days)."],
				datasets: [
				";
				foreach($types as $type) {
					$colors = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
					$punishments = array();
					for($day = 6; $day >= 0; $day--) {
						$rows = mysqli_num_rows(mysqli_query($con, "SELECT * FROM `".$info['history']."` WHERE ".($info['compact'] == true ? "punishmentType LIKE '%".strtoupper($type)."%'" : "punishmentType='".strtoupper($type)."'")." AND start BETWEEN '".(strtotime("-".$day." days") * 1000)."' AND '".(strtotime("-".($day - 1)." days") * 1000)."'"));
						if($type == 'all') {
							$rows = mysqli_num_rows(mysqli_query($con, "SELECT * FROM `".$info['history']."` WHERE start BETWEEN '".(strtotime("-".$day." days") * 1000)."' AND '".(strtotime("-".($day - 1)." days") * 1000)."'".($info['ip-bans'] == false ? " AND punishmentType!='IP_BAN' " : "")));
						}
						$punishments[] = $rows;
					}
					$sets[] = '
					{
						label: "'.strtoupper($lang[$type.($type != 'all' ? 's' : '')]).'",
						fill: false,
						data: ['.implode(", ", $punishments).'],
						borderColor: "rgb('.implode(", ", $colors).')",
						backgroundColor: "rgb('.implode(", ", $colors).')"
					}
					';
				}
				echo implode(", ", $sets);
				?>
				]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true
						}
					}]
				},
                title:{
                    display: true,
                    text: "<?php echo $lang['graph_title']; ?>"
                }
			}
		});
		</script>
	</body>
</html>
