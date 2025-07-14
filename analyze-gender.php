<?php

$buckets = [
	'< 1767' => [ 'male' => 0, 'female' => 0 ],
	'1767 - 1884' => [ 'male' => 0, 'female' => 0 ],
	'1885 - 1921' => [ 'male' => 0, 'female' => 0 ],
	'1922 - 1947' => [ 'male' => 0, 'female' => 0 ],
	'>= 1948' => [ 'male' => 0, 'female' => 0 ],
];

$genderTotals = [ 'male' => 0, 'female' => 0 ];

$numBornInBracket = 0;

$filename = $argv[1];
$handle = fopen( $filename, "r" );
while ( ( $curRow = fgetcsv( $handle, null, "\t" ) ) !== false ) {
	$birthYear = $curRow[2];
	$gender = $curRow[3];

	if ( $birthYear < 1767 ) {
		$birthRange = '< 1767';
	} elseif ( $birthYear < 1885 ) {
		$birthRange = '1767 - 1884';
	} elseif ( $birthYear < 1922 ) {
		$birthRange = '1885 - 1921';
	} elseif ( $birthYear < 1948 ) {
		$birthRange = '1922 - 1947';
	} else {
		$birthRange = '>= 1948';
	}

	if ( $gender == 'Q6581097' ) {
		$genderTotals['male']++;
		$buckets[$birthRange]['male']++;
	} elseif ( $gender == 'Q6581072' ) {
		$genderTotals['female']++;
		$buckets[$birthRange]['female']++;
	}

	if ( $birthYear >= 1950 && $birthYear <= 1970 ) {
		$numBornInBracket++;
	}
}

$totalMale = $genderTotals['male'];
$totalFemale = $genderTotals['female'];
$total = $totalMale + $totalFemale;
$percentMale = sprintf("%.1f%%", $totalMale / $total * 100);
$percentFemale = sprintf("%.1f%%", $totalFemale / $total * 100);
print "Total: $totalMale male ($percentMale), $totalFemale female ($percentFemale), $total total.\n";

foreach ( $buckets as $birthRange => $values ) {
	$numMale = $values['male'];
	$numFemale = $values['female'];
	$total = $numMale + $numFemale;
	if ( $total == 0 ) {
		print "$birthRange: No values found.\n";
	} else {
		$percentMale = sprintf("%.1f%%", $numMale / $total * 100);
		$percentFemale = sprintf("%.1f%%", $numFemale / $total * 100);
		print "$birthRange: $numMale male ($percentMale), $numFemale female ($percentFemale), $total total.\n";
	}
}

$total = $totalMale + $totalFemale;
$percentForBracket = sprintf("%.1f%%", $numBornInBracket / $total * 100);
print "Born between 1950 and 1970: $numBornInBracket ($percentForBracket)\n";
