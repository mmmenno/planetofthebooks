<?php

ini_set('memory_limit', '1024M');



$sparql = "
SELECT ?i ?iLabel (LANG(?iLabel) as ?langLabel) ?loc ?creatorLabel ?createdate ?locLabel ?coords ?img WHERE {
  VALUES ?klasse { wd:Q47461344 wd:Q7725634 wd:Q8261 }
  ?i wdt:P31 ?klasse .
  ?i wdt:P50 ?creator .
  ?i wdt:P577 ?createdate .
  optional{
    ?i wdt:P18 ?img .
  }
  ?i wdt:P840 ?loc .
  ?loc wdt:P17 wd:" . $qcountry . " .
  ?loc wdt:P625 ?coords .
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
order by DESC(?createdate)
limit 3000
";

$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


//echo $sparql;

//print_r($data);

//die;

$locations = array();

foreach ($data['results']['bindings'] as $k => $v) {


	if($scape == "nl" && $v['langLabel']['value'] != "nl"){
		continue;
	}

	$qid = str_replace("http://www.wikidata.org/entity/","",$v['loc']['value']);

	if(!isset($locations[$qid])){

		$locations[$qid] = array(
			"coords" => $v['coords']['value'],
			"label" => $v['locLabel']['value']
		);

	}

	$paintingid = str_replace("http://www.wikidata.org/entity/","",$v['i']['value']);
	$locations[$qid]['paintings'][$paintingid] = array(
		"title" => $v['iLabel']['value'],
		"img" => str_replace("http:","https:",$v['img']['value']),
		"maker" => $v['creatorLabel']['value'],
		"date" => substr($v['createdate']['value'],0,4)
	);

}

//print_r($locations);
//die;

$fc = array("type"=>"FeatureCollection", "features"=>array());

$beenthere = array();

foreach ($locations as $k => $v) {

	$loc = array("type"=>"Feature");
	$props = array(
		"wdid" => $k,
		"label" => $v['label'],
		"paintings" => $v['paintings'],
		"cnt" => count($v['paintings'])
	);
	
	
	$coords = str_replace(array("Point(",")"), "", $v['coords']);
	$latlon = explode(" ", $coords);
	$loc['geometry'] = array("type"=>"Point","coordinates"=>array((double)$latlon[0],(double)$latlon[1]));
	
	$loc['properties'] = $props;
	$fc['features'][] = $loc;

}

$json = json_encode($fc);

file_put_contents("geojson-" . $scape . "/" . $qcountry . '.geojson', $json);













