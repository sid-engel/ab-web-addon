<?php
require("../database.php");

if(isset($_GET['user'])) {
	$json = json_decode(file_get_contents("https://www.theartex.net/cloud/api/minecraft/?sec=uuid&username=".mysqli_real_escape_string($con, stripslashes($_GET['user']))),true);
	if($json["status"] == "error") {
		header('Location: index.php'); die("Redirecting...");
	}
} else {
	header('Location: ../'); die("Redirecting...");
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $lang['title']; ?> - <?php echo htmlspecialchars($_GET['user']); ?></title>
		<link rel="shortcut icon" href="../data/img/icon.png" type="image/x-icon">
		<link rel="icon" href="../data/img/icon.png" type="image/x-icon">
		<link rel="stylesheet" href="../data/css/ab-web-addon.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/css/materialize.min.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" href="../data/css/ab-web-addon.css">
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	</head>
	<body>
		<div class="navbar-fixed">
		<nav class="grey darken-4">
		<div class="nav-wrapper container">
				<a href="./" class="brand-logo left"><?php echo $lang['title']; ?></a>
				<ul class="right hide-on-med-and-down">
						<li><a href="./"><?php echo $lang['punishments']; ?></a></li>
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
								echo '<a href="/?type='.$type.'" class="waves-effect waves-light btn grey darken-4" style="margin: 3px;">'.strtoupper($lang[$type.($type != 'all' ? 's' : '')]).' <span>- '.mysqli_num_rows($result).'</span></a>';
							}
							?>
						</p>
		</div>
		<div class="container">
			<form method="get" action="../user/">
				<div class="input-group">
					<input type="text" maxlength="50" name="user" class="form-control" placeholder="<?php echo $lang['search']; ?>">
						<button class="btn btn-default grey darken-4" type="submit"><?php echo $lang['submit']; ?></button>
				</div>
			</form>
		</div>
		<div class="container">
			<div class="row card-panel" style="margin-top: 2%;">
				<div class="col s12 m4 center-align">
							<img src="https://crafatar.com/renders/body/<?php echo $json['data']['uuid']; ?>" alt="Skin Profile"></img>
				</div>
				<div class="col s12 m8">
					<h3 style="text-align: center;">
							<?php echo (isset($banned) ? htmlspecialchars($_GET['user']).$banned : htmlspecialchars($_GET['user'])."<small><br><br><span>".$lang['not_banned']."</span></small>"); ?>
					</h3>
				</div>
			</div>
		</div>
			<div class="container" style="padding-top: 1%;">
						<div class="table-wrapper">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th><?php echo $lang['reason']; ?></th>
										<th><?php echo $lang['operator']; ?></th>
										<th><?php echo $lang['date']; ?></th>
										<th><?php echo $lang['end']; ?></th>
										<th><?php echo $lang['type']; ?></th>
										<th><?php echo $lang['status']; ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$result = mysqli_query($con,"SELECT * FROM `".$info['history']."` WHERE name='".mysqli_real_escape_string($con, stripslashes($_GET['user']))."' ".($info['ip-bans'] == false ? "AND punishmentType!='IP_BAN' " : "")."ORDER BY id DESC LIMIT ".$page['min'].", 10");
									if(mysqli_num_rows($result) == 0) {
										echo '<tr><td>'.$lang['error_no_punishments'].'</td><td>---</td><td>---</td><td>---</td><td>---</td></tr>';
									} else {
										while($row = mysqli_fetch_array($result)) {
											$end = formatDate("F jS, Y", $row['end'])."<br><span>".formatDate("g:i A", $row['end'])."</span>";
											if($row['end'] == "-1") {
												$end = $lang['error_not_evaluated'];
											}
											$status = $lang['error_not_evaluated'];
											if(in_array($row['punishmentType'], array('BAN', 'TEMP_BAN', 'MUTE', 'TEMP_MUTE', 'IP_BAN', 'WARNING', 'TEMP_WARNING'))) {
												$status = $lang['inactive'];
												if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM `".$info['table']."` WHERE uuid='".$row['uuid']."' AND start='".$row['start']."'")) > 0 && ($row['end'] == "-1" || date("U", formatDate("F jS, Y g:i A", (microtime(true) / 1000))) < date("U", formatDate("F jS, Y g:i A", $row['end'])))) {
													$status = $lang['active'];
													if(($row['punishmentType'] == 'BAN' || ($info['ip-bans'] == true && $row['punishmentType'] == 'IP_BAN')) && !isset($banned)) {
														$banned = "<small><br><br><span>".$lang['permanently_banned']."</span></small>";
													} elseif($row['punishmentType'] == 'TEMP_BAN' && !isset($banned)) {
														$banned = "<small><br><br><span>".$lang['until'].formatDate("F jS, Y", $row['end'])." at ".formatDate("g:i A", $row['end'])."</span></small>";
													}
												}
											}
											echo "<tr><td>".$row['reason']."</td><td>".($info['skulls'] == true ? "<img src='https://crafatar.com/renders/head/".json_decode(file_get_contents("https://www.theartex.net/cloud/api/minecraft/?sec=uuid&username=".$row['operator']),true)['data']['uuid']."?scale=2&default=MHF_Steve&overlay' alt='".$row['operator']."'>" : "").$row['operator']."</td><td>".formatDate("F jS, Y", $row['start'])."<br><span>".formatDate("g:i A", $row['start'])."</span></td><td>".$end."</td><td>".$lang[strtolower($row['punishmentType'])]."</td><td>".$status."</td></tr>";
										}
									}
									?>
								</tbody>
							</table>
							<div class="text-center">
								<ul class='pagination'>
									<?php
									if($page['number'] > 1) {
										echo "<li><a href='?user=".mysqli_real_escape_string($con, stripslashes($_GET['user']))."&p=1'>&laquo; ".$lang['first']."</a></li>";
										echo "<li><a href='?user=".mysqli_real_escape_string($con, stripslashes($_GET['user']))."&p=".($page['number'] - 1)."'>&laquo; ".$lang['previous']."</a></li>";
									}
									$rows = mysqli_num_rows(mysqli_query($con,"SELECT * FROM `".$info['history']."` WHERE name='".mysqli_real_escape_string($con, stripslashes($_GET['user']))."' ".($info['ip-bans'] == false ? "AND punishmentType!='IP_BAN' " : "")."ORDER BY id DESC LIMIT ".$page['min'].", 10"));
									$pages['total'] = floor($rows / 10);
									if($rows % 10 != 0 || $rows == 0) {
										$pages['total'] = $pages['total'] + 1;
									}
									if($page['number'] < 5) {
										$pages['min'] = 1; $pages['max'] = 9;
									} elseif($page['number'] > ($pages['total'] - 8)) {
										$pages['min'] = $pages['total'] - 8; $pages['max'] = $pages['total'];
									} else {
										$pages['min'] = $page['number'] - 4; $pages['max'] = $page['number'] + 4;
									}
									if($pages['max'] > $pages['total']) {
										$pages['max'] = $pages['total'];
									}
									if($pages['min'] < 1) {
										$pages['min'] = 1;
									}
									$pages['count'] = $pages['min'];
									while($pages['count'] <= $pages['max']) {
										echo "<li ".($pages['count'] == $page['number'] ? 'class="active"' : '')."><a href='?user=".mysqli_real_escape_string($con, stripslashes($_GET['user']))."&p=".$pages['count']."'>".$pages['count']."</a></li>";
										$pages['count'] = $pages['count'] + 1;
									}
									if($rows > $page['max']) {
										echo "<li><a href='?user=".mysqli_real_escape_string($con, stripslashes($_GET['user']))."&p=".($page['number'] + 1)."'>".$lang['next']." &raquo;</a></li>";
										echo "<li><a href='?user=".mysqli_real_escape_string($con, stripslashes($_GET['user']))."&p=".$pages['total']."'>".$lang['last']." &raquo;</a></li>";
									}
									?>
								</ul>
							</div>
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
	</body>
</html>
