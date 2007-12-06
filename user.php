<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $RCSfile: common.php,v $
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include("config.php");
include('login.php');
include_once('common.php');

if ($session_OK) 
  {
  $userid = $_SESSION['cdash']['loginid'];
  $xml = "<cdash>";
  $xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
  $db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
  mysql_select_db("$CDASH_DB_NAME",$db);
  $xml .= add_XML_value("title","CDash - My Profile");

  $user = mysql_query("SELECT * FROM user WHERE id='$userid'");
  $user_array = mysql_fetch_array($user);
  $xml .= add_XML_value("user_name",$user_array["firstname"]);
  $xml .= add_XML_value("user_is_admin",$user_array["admin"]);
  
		// Go through the list of project the user is part of
  $project2user = mysql_query("SELECT projectid,role FROM user2project WHERE userid='$userid'");	
		while($project2user_array = mysql_fetch_array($project2user))
				{
				$projectid = $project2user_array["projectid"];
				$project_array = mysql_fetch_array(mysql_query("SELECT name FROM project WHERE id='$projectid'"));
				$xml .= "<project>";
				$xml .= add_XML_value("id",$projectid);
				$xml .= add_XML_value("role",$project2user_array["role"]); // 0 is normal user, 1 is maintainer, 2 is administrator, 3 is superadministrator
				$xml .= add_XML_value("name",$project_array["name"]);
				$xml .= "</project>";
				}
		
		// Go through the public projects
		$project = mysql_query("SELECT name,id FROM project WHERE id NOT IN (SELECT projectid as id FROM user2project WHERE userid='$userid' AND public='1')");
		while($project_array = mysql_fetch_array($project))
		  {
				$xml .= "<publicproject>";
				$xml .= add_XML_value("id",$project_array["id"]);
				$xml .= add_XML_value("name",$project_array["name"]);
				$xml .= "</publicproject>";
		  }
		
		//Go through the claimed sites		
		$claimedsiteprojects = array();
		$siteidwheresql = "";
		$claimedsites = array();
		$site2user = mysql_query("SELECT siteid FROM site2user WHERE userid='$userid'");
		while($site2user_array = mysql_fetch_array($site2user))
		  {
				$siteid = $site2user_array["siteid"];
				$site["id"] = $siteid;
				$site_array = mysql_fetch_array(mysql_query("SELECT name FROM site WHERE id='$siteid'"));
				$site["name"] = $site_array["name"];	
				$claimedsites[] = $site;
				
				if(strlen($siteidwheresql)>0)
				  {
						$siteidwheresql .= " OR ";
				  }
				$siteidwheresql .= " siteid='$siteid' ";
		  }
		
		 // Look for all the projects
		 if(mysql_num_rows($site2user)>0)
			  {
				 $site2project = mysql_query("SELECT projectid FROM build WHERE $siteidwheresql GROUP BY projectid");
				 while($site2project_array = mysql_fetch_array($site2project))
						 {
						 $projectid = $site2project_array["projectid"];
						 $project_array = mysql_fetch_array(mysql_query("SELECT name FROM project WHERE id='$projectid'"));
							$claimedproject = array();
							$claimedproject["id"] = $projectid;
							$claimedproject["name"] = $project_array["name"];
						 $claimedsiteprojects[] = $claimedproject;
						 }
					}
		
		/** Report statistics about the last build */
		function ReportLastBuild($type,$projectid,$siteid,$projectname)
		  {
				$xml = "<".strtolower($type).">";
				
		  // Find the last build 
				$build = mysql_query("SELECT submittime,id FROM build WHERE siteid='$siteid' AND projectid='$projectid' AND type='$type' ORDER BY submittime DESC LIMIT 1");
				if(mysql_num_rows($build) > 0)
						{
						$build_array = mysql_fetch_array($build);
						$buildid = $build_array["id"];
						
						// Express the date in terms of days (makes more sens)
						$builddate = strtotime($build_array["submittime"]." UTC");
						$days = round((time()-$builddate)/(3600*24));
						
						if($days<1)
						  {
								$day = "today";
						  }
						else if($days==1)
						  {
								$day = "yesterday";
						  }
						else
						  {
								$day = $days." days";
								}		
						$xml .= add_XML_value("date",$day);
      $xml .= add_XML_value("datelink","index.php?project=".$projectname."&date=".date("Ymd",$builddate+3600*24));
				
					
						// Configure
						$configure = mysql_query("SELECT status FROM configure WHERE buildid='$buildid'");
						if(mysql_num_rows($configure)>0)
								{
								$configure_array = mysql_fetch_array($configure);
								$xml .= add_XML_value("configure",$configure_array["status"]);
								if($configure_array["status"] != 0)
								  {
										$xml .= add_XML_value("configureclass","error");
								  }
								else
								  {
										$xml .= add_XML_value("configureclass","normal");
								  }
								}	
						else 
						  {
								$xml .= add_XML_value("configureclass","normal");
						  }
									
						// Update
						$update = mysql_query("SELECT buildid FROM updatefile WHERE buildid='$buildid'");
						$nupdates = mysql_num_rows($update);
      $xml .= add_XML_value("update",	$nupdates);
  
		    // Set the color
						if($nupdates>0)
						  {
								$xml .= add_XML_value("updateclass","error");
						  }
				  else
						  {
								$xml .= add_XML_value("updateclass","normal");
						  }
								
						// Find the number of errors and warnings
						$builderror = mysql_query("SELECT count(buildid) FROM builderror WHERE buildid='$buildid' AND type='0'");
						$builderror_array = mysql_fetch_array($builderror);
						$nerrors = $builderror_array[0];
						$xml .= add_XML_value("error",$nerrors);
						$buildwarning = mysql_query("SELECT count(buildid) FROM builderror WHERE buildid='$buildid' AND type='1'");
						$buildwarning_array = mysql_fetch_array($buildwarning);
						$nwarnings = $buildwarning_array[0];
						$xml .= add_XML_value("warning",$nwarnings);
						
						// Set the color
						if($nerrors>0)
						  {
								$xml .= add_XML_value("errorclass","error");
						  }
						else if($nwarnings>0)
						  {
								$xml .= add_XML_value("errorclass","warning");
						  }
				  else
						  {
								$xml .= add_XML_value("errorclass","normal");
						  }
								
						// Find the test
      $nnotrun_array = mysql_fetch_array(mysql_query("SELECT count(testid) FROM build2test WHERE buildid='$buildid' AND status='notrun'"));
      $nnotrun = $nnotrun_array[0];
      $nfail_array = mysql_fetch_array(mysql_query("SELECT count(testid) FROM build2test WHERE buildid='$buildid' AND status='failed'"));
      $nfail = $nfail_array[0];
      
						// Display the failing tests then the not run 
						if($nfail>0)
						  {
								$xml .= add_XML_value("testfail",$nfail);
								$xml .= add_XML_value("testfailclass","error");
						  }
						else if($nnotrun>0)
						  {
								$xml .= add_XML_value("testfail",$nnotrun);
								$xml .= add_XML_value("testfailclass","warning");
						  }
						else
								{
								$xml .= add_XML_value("testfail","0");
								$xml .= add_XML_value("testfailclass","normal");
								}
					$xml .= add_XML_value("NA","0");			
     }
				else
						{
						$xml .= add_XML_value("NA","1");
						}
						
				$xml .= "</".strtolower($type).">";
				
				return $xml;
		  }
		
		
		// List the claimed sites
		foreach($claimedsites as $site)
		  {
		  $xml .= "<claimedsite>";
				$xml .= add_XML_value("id",$site["id"]);
				$xml .= add_XML_value("name",$site["name"]);
			 
				$siteid = $site["id"];
				
				foreach($claimedsiteprojects as $project)
		    {
						$xml .= "<project>";
						
						$projectid = $project["id"];
						$projectname = $project["name"];
						
						$xml .= ReportLastBuild("Nightly",$projectid,$siteid,$projectname);
						$xml .= ReportLastBuild("Continuous",$projectid,$siteid,$projectname);
						$xml .= ReportLastBuild("Experimental",$projectid,$siteid,$projectname);
					
						$xml .= "</project>";
						}
				
				$xml .= "</claimedsite>";
		  }
				
		// Use to build the site/project matrix		
		foreach($claimedsiteprojects as $project)
		  {
				$xml .= "<claimedsiteproject>";
				$xml .= add_XML_value("id",$project["id"]);
				$xml .= add_XML_value("name",$project["name"]);
				$xml .= "</claimedsiteproject>";
	  	}
		
				
		if(@$_GET['note'] == "subscribedtoproject")
    {
    $xml .= "<message>You have subscribed to a project.</message>";
    }		
		else if(@$_GET['note'] == "subscribedtoproject")
    {
				$xml .= "<message>You have been unsubscribed from a project.</message>";
				}
				
  $xml .= "</cdash>";
  
  
  // Now doing the xslt transition
  generate_XSLT($xml,"user");
  }

?>
