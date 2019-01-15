<?php

/*
	return array((int)rating,(int)votes,)(int)top250,(int)year)
		   FALSE on error
*/
function imdb_get_rating($tt_id) {
	$GLOBALS['imdb_error'] = '';

  $opts = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Accept-language: en\r\n"
    )
  );

  $context = stream_context_create($opts);


	$html = file_get_contents(sprintf('http://www.imdb.com/title/tt%07d/', $tt_id), false, $context);
	if ($html === false) return false; // Some error

  $reg = '#<span itemprop="ratingValue">(.+?)</span>#is';

	if (preg_match($reg,$html,$matches)) {
		$rating = $matches[1];
	} else {
		$GLOBALS['imdb_error'] = 'Rating parsing error 1';
		$rating = 0;
	}

	$reg = '#itemprop="ratingCount">(.+?)</span>#is';

	if (preg_match($reg,$html,$matches)) {
		$votes = str_replace(",","",$matches[1]);
	} else {
		$GLOBALS['imdb_error'] = 'Votes parsing error 2';
		$votes = 0;
	}

	$top250 = 0;

	$top250_reg = '#/chart/top\?tt(\d+)"><strong>Top 250 \#(\d+)</#is';
	if (preg_match($top250_reg,$html,$matches)) {
		$top250 = $matches[2];
	}

	$year = 0;

	$year_reg = '#href="/year/(\d{4})/#is';
	if (preg_match($year_reg,$html,$matches)) {
		$year = $matches[1];
	}

  // Test with http://www.imdb.com/title/tt2179116/ that changes with the IP and doesn't take Accept-Language
  // In account

  $date_published = '0000-00-00';

  $reg = '#<h4 class="inline">Release Date:</h4>(.+?)\(#is';
  if (preg_match($reg, $html, $matches)) {
    $parsed_date = date_parse($matches[1]);

    if ($parsed_date !== false) {
      $date_published = $parsed_date["year"] . "-" . $parsed_date["month"] . "-" . $parsed_date["day"];
    }
  }

  $reg = '#<h4 class="inline">Opening Weekend:</h4>(?:\s*)\$(?:[\d,]*)(?:\s*)\(USA\)(?:\s*)<span class="attribute">\((.+?)\)<#is';
  if (preg_match($reg, $html, $matches)) {
    $parsed_date = date_parse($matches[1]);

    if ($parsed_date !== false) {
      $date_published = $parsed_date["year"] . "-" . $parsed_date["month"] . "-" . $parsed_date["day"];
    }
  }

	return array(
    'rating'=>$rating,'votes'=>$votes,
    'top250'=>$top250,'year'=>$year,
    'date_published'=>$date_published
  );
}

function event_new_imdb_entry_added($id) {
	q('INSERT IGNORE INTO imdb_tt_to_process(id) VALUES (:id)', array('id'=>$id));
}

function imdb_bayesian_rating($rating,$votes) {
	return ($votes / ($votes+3000)) * $rating + (3000/ ($votes+3000)) * 6.9;
}

function imdb_db_update($tt_id, $ratings) {
  q('UPDATE imdb_tt
     SET rating=:rating, votes=:votes,
         year=:year,
         last_update = NOW(), bayesian_rating=:bayesian_rating,
         date_published = :date_published
     WHERE id=:id',
      array('id'=>$tt_id, 'rating'=>((float)$ratings['rating'])*10, 'votes'=> $ratings['votes'],
          'year'=>$ratings['year'],
          'bayesian_rating'=>imdb_bayesian_rating($ratings['rating'],$ratings['votes'])*10,
          'date_published'=>$ratings['date_published']
      )
    );
}