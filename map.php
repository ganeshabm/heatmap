<html>
<head>
<title>Heatmaps</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<style>
 #mapLegend {
			background: #D5D8DC;
			color: #3c4750;
			padding: 0 10px 0 10px;
			top: 10px;
			font-weight: bold;
			filter: alpha(opacity=80);
			opacity: 0.7;
			border: 1px solid #000;
			width: 19%;
			height:12%;
		}
		#mapLegend div {
		  height: 40px;
		  line-height: 25px;
		  font-size: 1em;
		}
</style>
</head>
<body>
<div id="map" style="height:100%;width:100%"></div>
<div id="mapLegend" style="height:110px;top: 61px;z-index: 100;float: left;position: absolute;">
<span id="rsrpval"></span>
</div>
<script>
var map,infoWindow,heatmap,heatmap1,heatmap2;
var marker;
var heatmapvalue=[],heatmapvalue1=[],heatmapvalue2=[];
var rcolor,rcolor1,rcolor2;
var weight1=3;
var radius=30,count=0,indx =0;
var latlngval;
var newlatlnval=[],latlngArr =[],latlngvalArr=[];
var image;
var latlngbounds,primarycolor;
function initMap()
{
	map= new google.maps.Map(document.getElementById("map"),{
	zoom:17,
	mapTypeId:"satellite",
	});
	heatmap=new google.maps.visualization.HeatmapLayer(
	{
		map:map
	});
	marker = new google.maps.Marker(
	{
		map: map,
		icon:image
	});
	heatmap1=new google.maps.visualization.HeatmapLayer(
	{
		map:map
	});
	heatmap2=new google.maps.visualization.HeatmapLayer(
	{
		map:map
	});
	image = {
        url: "markernew.png",
        scaledSize: new google.maps.Size(48, 60),
	};
	latlngbounds = new google.maps.LatLngBounds();
	ajaxcall("action=readarr",getPoints);
}

function ajaxcall(datastring,callback)
{  
	$.ajax({
		type:'post',
		url:'latlngval.php',
		data:datastring,
		cache:false,
		async:true,
		success: function(result)
		{
			if(callback != null)
			{
				callback(JSON.parse(result));
			}
		}
	});
}

function getPoints(res) 
{
	//console.log(res);
	latlngArr =  res[0].output.split("|");
	for (var i = 0; i < latlngArr.length; i++) 
	{
		var val=latlngArr[i].trim().split(",");
		latlngbounds.extend(new google.maps.LatLng(parseFloat(val[0]),parseFloat(val[1])));
	}
	map.fitBounds(latlngbounds);
	setInterval(plotHM,500);
}

function plotHM()
{
	if(latlngArr[indx] == null || latlngArr[indx].trim() == "")
		return;
	
	if(indx!=0 && latlngArr[indx] != null)
	marker.setPosition(null);
	latlngval=latlngArr[indx].trim().split(",");
	//console.log("LATLNG VAL    :"+latlngval+ " == COUNT    :"+indx);
	newlatlnval=[];
	//map.panTo( new google.maps.LatLng(parseFloat(latlngval[0]),parseFloat(latlngval[1])) );
	marker.setPosition(new google.maps.LatLng(parseFloat(latlngval[0]),parseFloat(latlngval[1])));
	document.getElementById("rsrpval").innerHTML= "RSRP   :" +parseFloat(latlngval[2]);
		document.getElementById("rsrpval").style.color = rcolorval(parseFloat(latlngval[2]));
		primarycolor=rcolorval(parseFloat(latlngval[2]));
		document.getElementById("rsrpval").style.fontSize = "x-large";
	distancefind(latlngval[0],latlngval[1]); 
}

function distancefind(lat,lng)
{
	for(var j=0;j<latlngArr.length;j++)
	{
		latlngvalArr=latlngArr[j].split(",");
		var distfeet = distance(lat,lng,latlngvalArr[0], latlngvalArr[1]);
		////console.log("Distance    :"+distfeet);
		if (distfeet <= 500)
		{
		newlatlnval.push({'lat':latlngvalArr[0],'lon':latlngvalArr[1],'rsrp':latlngvalArr[2],'dist':distfeet});
		//console.log(newlatlnval[0]['lat']);		
		}  
	}
	newlatlnval.sort(function(a, b)
	{
		return a.dist - b.dist;
	});
	//console.log("new:"+JSON.stringify(newlatlnval));
	getheatmap(newlatlnval);
}

function distance(lat1, lon1, lat2, lon2, unit) 
{
	var R = 6371; // Radius of the earth in km
	var dLat = deg2rad(lat2-lat1);  // deg2rad below
	var dLon = deg2rad(lon2-lon1); 
	var a = Math.sin(dLat/2) * Math.sin(dLat/2) +Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2); 
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
	var d = R * c; // Distance in km
	return d * 5280;//Distance in Feet
}

function deg2rad(deg)
{
	return deg * (Math.PI/180)
}

function getheatmap(newlatlnval)
{
	if(indx!=0)
	{
		heatmap.setData([]);
		heatmap1.setData([]);
		heatmap2.setData([]);
		heatmapvalue=[];
		heatmapvalue1=[];
		heatmapvalue2=[];
	}
	for(var k=0;k<newlatlnval.length;k++)
	{
		
		var range1=rcolorval(newlatlnval[k]['rsrp']);
		//console.log(newlatlnval[k]['lat']);
		if(newlatlnval[k+1] !=null)
		{
			var range2=rcolorval(newlatlnval[k+1]['rsrp']);
			console.log("range1:"+range1+" range2:"+range2);
			if(range2==range1)
				weight1=3;
			else
				weight1=1;
			console.log("weight:"+weight1);
		}
		if(range1=="green")
		{
			heatmapvalue.push({location:new google.maps.LatLng(parseFloat(newlatlnval[k]['lat']),parseFloat(newlatlnval[k]['lon'])),weight:0.03});
			heatmap.setData(heatmapvalue);
		}
		else if(range1=="yellow")
		{
			heatmapvalue1.push({location:new google.maps.LatLng(parseFloat(newlatlnval[k]['lat']),parseFloat(newlatlnval[k]['lon'])),weight:0.1});
			heatmap1.setData(heatmapvalue1);
		}
		else if(range1=="red" )
		{
			heatmapvalue2.push({location:new google.maps.LatLng(parseFloat(newlatlnval[k]['lat']),parseFloat(newlatlnval[k]['lon'])),weight:1});
			heatmap2.setData(heatmapvalue2);
		}
	}
		
		
		heatmap2.set("radius",radius);
		
		
		heatmap.set("radius",radius);
		
		
		heatmap1.set("radius",radius);
		
	indx++;
}

function rcolorval(rsrpval)
{
	range="green";
	if((rsrpval<=-50) && (rsrpval>=-89))
	{
		rcolor=[
		'rgba(221, 249, 105, 0)',
		'rgba(221, 249, 105, 1)',
		'rgba(34, 139, 34, 1)',	
  ];// green
	}
	else if((rsrpval <=-90) && (rsrpval>=-105))
	{
		range="yellow";
		rcolor1= [
		'rgba(255, 154, 0, 0)',
		'rgba(255, 154, 0, 1)',
		'rgba(255, 206, 0, 1)',
		'rgba(240, 255, 0, 1)'
  ]; // yellow
	}
	else
	{
		range="red";
		rcolor2=[
      	'rgba(255, 0, 0, 0)',
		'rgba(255, 0, 0, 1)',
		'rgba(255, 0, 0, 1)',
  ];
	}
	return range;
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD8qE7UJKblyrSKsbA3R_T6nJlAzTbqHrE&callback=initMap&libraries=visualization&v=weekly"
defer ></script>
</body>
</html>