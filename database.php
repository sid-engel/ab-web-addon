<?php
session_start(); //Sessions data is saved for accounts.
ob_start(); //Static content loads first.
//This is required for account data to be saved.

$con = mysqli_connect("localhost","root","","ab-web-addon-master");
//Enter your MYSQL details here.

//Change the language.
$lang = array(
	//Information
	'title'=>'AdvancedBan Web Addon',
	'description'=>'A simple, but sleek, web addon for AdvancedBan.',

	//General
	'punishments'=>'Punishments',
	'credits'=>'Credits',
	'search'=>'Search for...',
	'submit'=>'Submit',
	'permanently_banned'=>'Permanently Banned',
	'until'=>'Banned until ',
	'not_banned'=>'Not Banned',
	'inactive'=>'Inactive',
	'active'=>'Active',

	//Graphs
	'graphs'=>'Graphs',
	'graph_title'=>'7 Days of Punishments',

	//Pages
	'first'=>'First',
	'previous'=>'Previous',
	'next'=>'Next',
	'last'=>'Last',

	//List
	'username'=>'Username',
	'reason'=>'Reason',
	'operator'=>'Operator',
	'date'=>'Date',
	'end'=>'End',
	'type'=>'Type',
	'status'=>'Status',

	//Punishment
	'ban'=>'Ban',
	'temp_ban'=>'Temp. Ban',
	'ip_ban'=>'IP Ban',
	'mute'=>'Mute',
	'temp_mute'=>'Temp. Mute',
	'warning'=>'Warning',
	'temp_warning'=>'Temp. Warning',
	'kick'=>'Kick',

	//Punishments
	'all'=>'All',
	'bans'=>'Bans',
	'temp_bans'=>'Temp. Bans',
	'ip_bans'=>'IP Bans',
	'mutes'=>'Mutes',
	'temp_mutes'=>'Temp. Mutes',
	'warnings'=>'Warnings',
	'temp_warnings'=>'Temp. Warnings',
	'kicks'=>'Kicks',

	//Errors
	'error_no_punishments'=>'No punishments could be listed on this page.',
	'error_not_evaluated'=>'N/A',
	);

//-----------------------------------------------------------------------------------
// (!) The following portion of the database.php file does not require changes. (!)
//-----------------------------------------------------------------------------------

if (mysqli_connect_errno()) {
	die('Failed to connect to database.'); //Restrict access to any page if no connection is established.
}

//Set up a default structure for monitoring pages.
$page = array('max'=>10, 'min'=>0, 'number'=>1, 'posts'=>0, 'count'=>0);

//Set up a structure for monitoring pages based on user input.
if(isset($_GET['p']) && is_numeric($_GET['p'])) {
	$page = array('max'=>$_GET['p']*10,'min'=>($_GET['p'] - 1)*10,'number'=>$_GET['p'],'posts'=>0,'count'=>0);
}

//List the types of punishments.
$types = array('all','ban','temp_ban','mute','temp_mute','warning','temp_warning','kick');
if($info['ip-bans'] == true) {
	$types[] = 'ip_ban';
}
if($info['compact'] == true) {
	$types = array('all','ban','mute','warning','kick');
}

//Use an external API to display times relative to the user's timezone.
if(!isset($_SESSION['time_zone'])) {
	$_SESSION['time_zone'] = "America/Los_Angeles";
	$tz_api = json_decode(file_get_contents('http://freegeoip.net/json/'.$_SERVER['REMOTE_ADDR']), true);
	if(isset($tz_api['time_zone']) && in_array($tz_api['time_zone'], timezone_identifiers_list())) {
		$_SESSION['time_zone'] = $tz_api['time_zone'];
	}
}

//Function for handling times.
function formatDate($format, $ms) {
	$date = new DateTime(gmdate("F jS, Y g:i A", $ms / 1000));
	$date->setTimezone(new DateTimeZone($_SESSION['time_zone']));
	return $date->format($format);
}
