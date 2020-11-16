<?php

require_once 'vendor/autoload.php';
use \YaLinqo\Enumerable;

$dbms='mysql';     
$host='localhost'; 
$dbName='trip';    
$user='root';      
$pass='password';       
$dsn="$dbms:host=$host;dbname=$dbName";

$page=$_POST["page"];



try {
	$connection   = new PDO($dsn, $user, $pass); 
	

	$statement = $connection->query('SELECT * FROM  trip.itinerary 
	LEFT JOIN trip.tour_group ON trip.itinerary.tripid = trip.tour_group.tripId 
	LEFT JOIN trip.trip_tag ON trip.itinerary.tripid = trip.trip_tag.tripId
	LEFT JOIN trip.trip_agency ON trip.itinerary.agencyid = trip.trip_agency.agencyId
	LEFT JOIN trip.itinerary_tag ON trip.itinerary_tag.tagId=trip.trip_tag.tagId
	ORDER BY trip.itinerary.oriPrice ,trip.tour_group.tripScore
	LIMIT ('.$page.'-1)*10, 20'
	);

	$statement2=$connection->query('SELECT *
	FROM trip.itinerary');


    $dataarray  = array();
    $dataarray2  = array();
	
	foreach($statement as $row){
		
		$data = [

		'tripid' =>$row['tripid'],//行程流水號
		'tripname' =>$row['tripName'],//行程名稱
		'totalday' =>$row['totalDay'],//總天數
		'dicount' =>$row['oriPrice']-$row['nowPrice'],//現賺
		'price'=>$row['nowPrice'],
		'group'=>$row['tourGroupId'],//團
		'start'=>$row['startDate'],//開始日期
		'end'=>$row['endDate'],//結束日期
		'saleseat'=>$row['totalPeople']	-$row['reserve'],//可售人數
		'score'=>$row['tripScore'],//評分
		'location'=>$row['description'],//地點
		'agency'=>$row['agencyName'],//旅行社
		'tag'=>$row['tagName']//標籤

		];
		
		array_push ($dataarray,$data);	
	}

	foreach($statement2 as $row2){

		$data2=[

		'tripid' =>$row2['tripid']

		];

		array_push ($dataarray2,$data2);	

	}
	
	$result = from($dataarray)
		->groupBy('$v["tripid"]','$v["tripname"]','$item ==> $item["agency"]');

	$result_echo=$result->toArrayDeep();

	
	echo json_encode($result_echo,JSON_UNESCAPED_UNICODE);

    $connection  = null;
} catch (PDOException $e) {
    die ("Error!: " . $e->getMessage() . "<br/>");
}
//默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数：array(PDO::ATTR_PERSISTENT => true) 变成这样：
$db = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true));
?>