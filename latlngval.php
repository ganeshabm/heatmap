<?php
	
	if(isset($_POST['action']))
	{
		if($_POST['action']=='readarr')
		{
			/* $latlngArr = array();
			array_push($latlngArr,array("output"=>file_get_contents('latlngval.txt ')));
			echo json_encode($latlngArr); */
			$filesArray = array();
			$output = file_get_contents('latlngval.txt');
			array_push($filesArray,array("output"=>$output));
			echo json_encode($filesArray);
		}
	} 
?>
