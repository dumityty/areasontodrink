<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>A Reason To Drink</title>
</head>

<body>

	<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	$cheers = array(
		"Noroc",
		"Cheers",
		"Cin Cin",
		"į sveikatą",
		"Na zdrowie",
		"Saúde",
		"Na zdravie",
		"Salud",
		"Iechyd da",
		"Priekā",
		"Kanpai (乾杯)",
		"Skál",
		"Egészségedre",
		"Prost / Zum wohl",
		"Santé",
		"Mabuhay",
		"Å’kålè ma’luna",
	);

	$today = date("M_d");

	$wiki = "http://en.wikipedia.org/";
	$wiki .= "/w/api.php?action=query&prop=extracts&format=json&indexpageids=&titles=$today&rawcontinue=";

	$events = @file_get_contents($wiki);

	$events = json_decode($events);

	var_dump($events);

	$pageid = $events->query->pageids[0];

	$extract = $events->query->pages->$pageid->extract;

	$uls = getUls($extract);


	function getLis($ul){
		$i = 0;
		while (strpos($ul, "<li>") !== FALSE){
			$listart = strpos($ul, "<li>");
			$liend = strpos($ul, "</li>");
			$listart = $listart + 4;
			$lengthli = $liend - $listart;
			$licontent[$i] = substr($ul, $listart, $lengthli);

			$liend = $liend + 5;
			$ul = substr($ul, $liend);
			$i++;
		}

		return $licontent;
	}


	function getUls($extract){
		$i = 0;
		while (strpos($extract, "<ul>") !== FALSE){
			$ulstart = strpos($extract, "<ul>");
			$ulend = strpos($extract, "</ul>");
			$ulstart = $ulstart + 4;
			$lengthul = $ulend - $ulstart;
			$ulcontent[$i] = substr($extract, $ulstart, $lengthul);

			$ulend = $ulend + 5;
			$extract = substr($extract, $ulend);
			$i++;
		}

		// 0 - events
		// 1 - births
		// 2 - deaths
		// 3 - holidays and observances
		return $ulcontent;
	}

	?>

	<center>
		<h1>A reason to drink on:</h1>
		<h2><?php echo date("M d, Y"); ?></h2>
		<br />
		<h3>
			<?php

				$what = mt_rand(0,3);
					// we don't want deaths as a reason to drink
				while ($what == 2){
					$what = mt_rand(0,3);
				}

				switch ($what){
					case 0:
						$what_str = "Event";
						break;
					case 1:
						$what_str = "Birth";
						break;
					case 2:
						$what_str = "Death";
						break;
					case 3:
						$what_str = "Holiday";
						break;
				}

				// 0 - events
				// 1 - births
				// 2 - deaths
				// 3 - holidays and observances

					// get all lis of a specific ul
				$lis = getLis($uls[$what]);
				$lengthlis = count($lis);
				//$which = rand(0,($lengthlis-1));
				$which = mt_rand(0,($lengthlis-1));
				echo $what_str . ": " . $lis[$which];

			?>
		</h3>

		<br />
		<br />
		<h2>
			<?php
				$lengthcheers = count($cheers);
				$cheers_key = mt_rand(0, ($lengthcheers-1));
				echo $cheers[$cheers_key] . "!";
			?>
		</h2>
	</center>



</body>

</html>
