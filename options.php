<?


if($scape == "city"){
    $qscape = "Q1935974";
}else{
    $qscape = "Q191163";
}


$sparql = "
SELECT ?country ?countryLabel (COUNT(DISTINCT(?i)) AS ?nr) WHERE {
  VALUES ?klasse { wd:Q47461344 wd:Q7725634 wd:Q8261 }
  ?i wdt:P31 ?klasse .
  ?i wdt:P840 ?loc .
  ?loc wdt:P625 ?coords .
  ?loc wdt:P17 ?country .
  MINUS { ?country wdt:P576 ?end }
  #?i wdt:P18 ?afb .
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"en\". }
}
GROUP BY ?country ?countryLabel
ORDER BY ASC(?countryLabel)
";

$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);



$options = "";

foreach ($data['results']['bindings'] as $k => $v) {

    $qnr = str_replace("http://www.wikidata.org/entity/","",$v['country']['value']);

    $cl = $v['countryLabel']['value'];
    if(strlen($cl)>28){
        $cl = substr($v['countryLabel']['value'],0,28) . "...";
    }

    if($qcountry==$qnr){
        $options .= "<option selected=\"s\" value=\"" . $qnr . "\">";
        $options .= $cl . " (" . $v['nr']['value'] . ")</option>\n";
        $countryname = $v['countryLabel']['value'];
    }else{
        $options .= "<option value=\"" . $qnr . "\">";
        $options .= $cl . " (" . $v['nr']['value'] . ")</option>\n";
    }

}
