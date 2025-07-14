<?php

function getPropertyValue( $wikidataID, $propertyID ) {
//	print "$wikidataID. ";
	$apiURL = "https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=$wikidataID&property=$propertyID&format=json";
	$valueJSON = file_get_contents( $apiURL );
	$dataValue = json_decode( $valueJSON );
	if ( !property_exists( $dataValue->claims, $propertyID ) ) {
		return '';
	}
//	if ( !is_array( $dataValue->claims{$propertyID} ) ) {
//		return '';
//	}
	if ( !property_exists( $dataValue->claims->{$propertyID}[0]->mainsnak, 'datavalue' ) ) {
		return '';
	}
	$mainValue = $dataValue->claims->{$propertyID}[0]->mainsnak->datavalue->value;
	if ( $propertyID == 'P569' ) {
		return $mainValue->time;
	} else {
		return $mainValue->id;
	}
}

function printLineForWikidataID( $personName, $wikidataID ) {
	$gender = getPropertyValue( $wikidataID, 'P21' );
	if ( $gender == '' ) {
		return;
	}
	$birthDate = getPropertyValue( $wikidataID, 'P569' );
	if ( $birthDate == null ) {
		$birthYear = '';
	} else {
		$birthYear = str_replace( '+', '', substr( $birthDate, 0, strpos( $birthDate, '-', 1 ) ) );
	}
	print "$personName\t$wikidataID\t$birthYear\t$gender\n";
}

$level = $argv[1];

if ( !in_array( $level, [ '3', '4', '5', 'every', 'every2' ] ) ) {
	die ( "Argument to this command must be one of '3', '4', '5', 'every', or 'every2'.\n" );
}

if ( $level == 'every' || $level == 'every2' ) { // "Articles every Wikipedia should have"
	if ( $level == 'every' ) {
		$mainURL = 'https://meta.wikimedia.org/wiki/List_of_articles_every_Wikipedia_should_have';
	} else {
		$mainURL = 'https://meta.wikimedia.org/wiki/List_of_articles_every_Wikipedia_should_have/Expanded/People';
	}
	$mainHTML = file_get_contents( $mainURL );
	preg_match_all( '/https:\/\/www.wikidata.org\/wiki\/(Q\d*)[^<]*\>([^<]*)\</', $mainHTML, $matches );
	$allIDs = $matches[1];
	$allNames = $matches[2];
	foreach ( $allIDs as $i => $wikidataID ) {
		$personName = $allNames[$i];
		printLineForWikidataID( $personName, $wikidataID );
		if ( $level == 'every' && $personName == 'Zheng He' ) {
			break;
		}
	}
	return;
}

if ( $level == 3 ) {
	$mainURL = 'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/3';
	$mainHTML = file_get_contents( $mainURL );
} elseif ( $level == 4 ) {
	$mainURL = 'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/4/People';
	$mainHTML = file_get_contents( $mainURL );
} else { // $level == 5
	$allURLs = [
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Writers_and_journalists',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Artists,_musicians,_and_composers',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Entertainers,_directors,_producers,_and_screenwriters',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Philosophers,_historians,_political_and_social_scientists',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Religious_figures',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Politicians_and_leaders',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Military_personnel,_revolutionaries,_and_activists',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Scientists,_inventors,_and_mathematicians',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Sports_figures',
		'https://en.wikipedia.org/wiki/Wikipedia:Vital_articles/Level/5/People/Miscellaneous'
	];
	$mainHTML = '';
	foreach ( $allURLs as $curURL ) {
		$mainHTML .= file_get_contents( $curURL );
	}
}

preg_match_all( '/\/wiki\/([^"]*)/', $mainHTML, $matches );
$allPageNames = $matches[1];
foreach ( $allPageNames as $curPageName ) {
	if ( strpos( $curPageName, ':' ) > 0 ) {
		continue;
	}
	if ( in_array( $curPageName, [ 'Main_Page', 'Terms_of_Use', 'Privacy_policy' ] ) ) {
		continue;
	}

	$personName = urldecode( str_replace( '_', ' ', $curPageName ) );
	$personHTML = file_get_contents( 'https://en.wikipedia.org/wiki/' . $curPageName );
	if ( !$personHTML ) {
		continue;
	}
	preg_match( '/https:\/\/www.wikidata.org\/wiki\/Special:EntityPage\/(Q\d*)/', $personHTML, $matches2 );
	$wikidataID = $matches2[1];
	printLineForWikidataID( $personName, $wikidataID );

	if ( $level == 3 && $curPageName == 'Henry_Ford' ) {
		break;
	}
}
