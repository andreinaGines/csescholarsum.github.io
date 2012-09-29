<?php

	include("../includes/config.php");
	include("../tools/functions.php");

	connect_to_db();
?>

<table class='data_table' cellspacing="4" style="width: 100%">
    	<th>Name</th>
        <th>Email</th>
        <th>Major</th>
        <th>Graduation</th>
        <th>Resume</th>
		<th>Events Attended</th>
		<th>Service Hours</th>
<?php


	//gets the number
	$query = mysql_query("
		SELECT
			m.id, 
			m.name, 
			m.uniqname, 
			m.gradMonth, 
			m.gradYear, 
			m.showResume, 
			m.hasResume, 
			m.major, 
			m.hidden 
		FROM 
			members m
		WHERE 
			m.deleted = 0 
		ORDER BY m.uniqname
	");

	$event_query = mysql_query("
		SELECT
			COUNT( * ) 
		FROM 
			attendies a,
			EVENTS e,
			members m
		WHERE 
			m.deleted = 0 
			AND a.deleted = 0 
			AND e.eventID = a.eventID
			AND e.SerHours = 0
			AND e.deleted = 0
			AND a.uniqname = m.uniqname
		GROUP BY a.uniqname
		ORDER BY m.uniqname
	");

	$serv_query = mysql_query("
		SELECT SUM( e.SerHours )
		FROM 
			attendies a,
			EVENTS e
		WHERE 
			m.deleted = 0 
			AND a.deleted = 0 
			AND e.eventID = a.eventID
			AND e.deleted =0
			AND m.uniqname = a.uniqname
		GROUP BY a.uniqname
		ORDER BY m.uniqname
	");

#checks if db doesn't open
if (mysql_num_rows($query) == 0)
{
?>
	<tr>
    	<td>-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
<?php
}
else
{

	#This indexes through the db to print member info
	
	while ($userData = mysql_fetch_row($query))
	{

		list($id, $name, $uniqname, $gradMonth, $gradYear, $showResume, $hasResume, $major, $hidden) = $userData;
		if ($name == "")
			$name = "No Name";
		$member_email = $uniqname."@umich.edu";
		if ($hasResume && $showResume)
		{
			$resumeLink = "./resumes/index.php?uniqname=".$uniqname;
			$resumeLink = "<a target='_BLANK' href=\"".$resumeLink."\">View Resume</a>";
		}
		else {
			$resumeLink = "-------";
		}
		
		#this grabs the event table and updates events and service hours
		$eventData = mysql_fetch_row($event_query);
		list($numEvents) = $eventData;
		$servData = mysql_fetch_row($serv_query);
		list($numService) = $servData;

    #fix to avoid having 0 service hours
    if ($numService == 0) {
      $numService = "-";
    }

    if ($numEvents == 0) {
      $numEvents = "-";
    }
		
		#printing info into table
		echo "\t<tr>\n";
		echo "\t\t<td>$name</td>\n";
		echo "\t\t<td>".$member_email."</td>\n";
		echo "\t\t<td>".$major."</td>\n";
		if (($gradMonth < 1)||($gradMonth > 12))
			echo "\t\t<td>&nbsp;</td>\n";
		else
			echo "\t\t<td>".$gradMonth."/".$gradYear."</td>\n";
		echo "</td>\n";
		echo "\t\t<td>".$resumeLink."</td>\n";
		echo "\t\t<td style=\"text-align:center;\">".$numEvents."</td>\n";
		echo "\t\t<td style=\"text-align:center;\">".$numService."</td>\n";
		echo "\t</tr>\n";
	}
}

#close database

?>
</table>