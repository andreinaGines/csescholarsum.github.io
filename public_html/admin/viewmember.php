<?php

include("config.php");
include("../functions.php");

$user = $_GET['user'];

$query = mysql_query("SELECT * FROM members WHERE uniquename = '$user'");

if (mysql_num_rows($query) == 0)
{
	//no user exists
	$userExists = false;
}
else
{
	$userExists = true;

	$userData = mysql_fetch_row($query);
	
	list($id, $name, $uniqname, $gradMonth, $gradYear, $showResume, $hasResume, $major) = $userData;
	
	if (isset($_POST['name']))
	{
		//profile update; error detection/correction needed
		$name = mysql_real_escape_string($_POST['name']);
		$gradMonth = (int) $_POST['gradMonth'];
		$gradYear = (int) $_POST['gradYear'];
		$major = mysql_real_escape_string($_POST['major']);
		if (($major != "CE")&&($major != "CSE")&&($major != "CS_LSA"))
			$major = "";
		if ($_POST['resumeVisible'] == "on")
			$showResume = 1;
		else
			$showResume = 0;
		mysql_query("UPDATE members SET name = '$name', gradMonth = '$gradMonth', gradYear = '$gradYear', showResume = '$showResume', major = '$major'
			WHERE uniquename = '$uniqname'");
	}
	
	include("../includes/uploadResume.php");
	
	if (isset($_POST['deleteResume']))
	{
		//delete user resume; update db and remove file
		mysql_query("UPDATE members SET hasResume = '0' WHERE uniquename = '$uniqname'");
		$hasResume = 0;
		DeleteResume($uniqname);
	}
}

$name = stripslashes($name);

$pageTitle = "Admin Section";
$indirection = "../";
include("./admin_top.php");
include ("../top.php");
include("adminMenu.php");

if ($userExists)
{
if ($uploadFailed)
{
	if ($notPDF)
		echo "Resume must be a pdf!<br /><br />\n\n";
	else
		echo "File upload failed!<br /><br />\n\n";
}
echo "Name: ".$name."<br />\n";
echo "Uniqname: ".$uniqname."<br />\n";
echo "Major: ".$major."<br />\n";
echo "Graduation: ".FormatGradDate($gradMonth, $gradYear)."<br />\n";
echo "Has Resume: ".$hasResume."<br />\n";
echo "Show Resume: ".$showResume."<br /><br /><br />\n\n";

?>

<form name="updateUser" method="post" action="viewmember.php?user=<?php echo $user; ?>">
    <table>
    <tr><td>Name: </td><td><input name="name" type="text" value="<?php echo $name; ?>"  /></td></tr>
    <tr>
    	<td>Major: </td>
    	<td>
        	<select name="major">
            	<option value=""></option>
            	<option value="CSE"<?php if ($major == "CSE") echo " selected=\"selected\""; ?>>CSE</option>
                <option value="CE"<?php if ($major == "CE") echo " selected=\"selected\""; ?>>CE</option>
                <option value="CS-LSA"<?php if ($major == "CS-LSA") echo " selected=\"selected\""; ?>>CS_LSA</option>
            </select>
    	</td>
    </tr>
    <tr><td>Graduation: </td><td><input name="gradMonth" type="text" maxlength="2" size="3" value="<?php echo $gradMonth; ?>"  />, <input name="gradYear" type="text" maxlength="4" size="4" value="<?php echo $gradYear; ?>"  /> (mm yyyy)</td></tr>
    <tr><td>Resume Visible: </td><td><input name="resumeVisible" type="checkbox" <?php if ($showResume) echo "checked=\"checked\""; ?>  /></td></tr>
    <tr><td>&nbsp;</td><td><input type="submit" value="Update Profile" /></td></tr>
    </table>
</form>
<br /><br />
<?php
if ($hasResume)
{
	echo "\n<a href=\"../resumes/".$uniqname.".pdf\">View Resume</a>\n";
?>
<form name="deleteResumeForm" action="viewmember.php?user=<?php echo $user; ?>" method="post">
<input type="submit" name="deleteResume" value="Delete Resume" />
</form>
<?php
}
else
{
	echo "\nNo resume on file\n";
}
?>
<br /><br />
<form enctype="multipart/form-data" name="uploadResume" method="post" action="viewmember.php?user=<?php echo $user; ?>">
	Resume: <input type="file" name="resumeFile"  /> (must be a pdf, will overwrite existing resume)<br />
    <input type="submit" value="Upload Resume" />
</form>

<?php
}
else
{
	//user does not exist
	echo "User does not exist";
}
?>
<br />
<br />
<form name="deleteResumeForm" action="index.php?delete=<?php echo $user; ?>" method="post">
<input type="submit" name="deleteResume" value="Delete Member" />
</form>

<?php
include ("../side.php");
include ("../bottom.php");
?>
