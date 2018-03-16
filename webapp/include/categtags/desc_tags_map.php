<?php
/*
Format:
inputName=>array( type, [ param ] )
types:
	Associated string can be a range(separated with an -), or a list separated with a comma ,
	"tag", then param is the associated tag_id
	"map", then the param is an array where
						key is the tag_id and
						value is associated string.
	"branch", then a select should be made for that tag, name_ro, name_ru, name_en(if not empty) will be the associated string
	"mix", param should be an array with any of types listed above
*/
$tagMap = array(
	'serial'=>array('tag',45),
	'subtitles'=>array('tag',29),
	'year'=>array('map',
		array(
			'51'=>'2009',
			'52'=>'2005-2008',
			'53'=>'2000-2004',
			'54'=>'1995-1999',
			'55'=>'1990-1994',
			'56'=>'1980-1989',
			'57'=>'1970-1979',
			'58'=>'1960-1969',
			'59'=>'1950-1959',
			'60'=>'1930-1949',
			'61'=>'1890-1929',
			'189'=>'2010',
			'190'=>'2011',
			'197'=>'2012',
			'701'=>'2013',
			'702'=>'2014',
			'704'=>'2015',
			'707'=>'2016',
			'708'=>'2017',
		)
	),
	'country'=>array('branch', 164),
/*
Russian: AC3, 320 kb/s (6 ch) / English: DTS, 1536 kb/s (6 ch)
русский дублированный (AC3 5.1 640kbps), английский (DTS-HD MA 7.1 + DTS 5.1 core 1536kbps), комментарии (AC3 2.0 192kbps)
Русский (AC3, 6 ch, 448 Кбит/с), русский (AC3, 6 ch, 640 Кбит/с)[Профессиональный многоголосый - отдельно], английский (DTS, 6 ch, 1536 Кбит/с)[отдельно]
Русский (DTS, 6 ch, 768 Кбит/с), английский (DTS, 6 ch, 768 Кбит/с)
Русский (АС3, 6 ch, 448 Кбит/с), английский (DTS, 6 ch, 1536 Кбит/с)
Русский (DTS, 6 ch, 768 Кбит/сек, 48,0 КГц), английский (DTS, 6 ch, 1536 Кбит/сек, 48,0 КГц)
AAC 48000Hz stereo 126Kbps
Russian (DTS, 5.1, 768 kbps / 48 kHz / 16-bit), Ukrainian: AC3 5.1, 384 kbps; - (Профессиональный (Многоголосый)), |Лицензия R5|, English (DTS-HD, 7.1 / 48 kHz / 5647 kbps / 16-bit (DTS Core: 5.1 / 48 kHz / 1509 kbps / 16-bit))
AC3 2.0 448 Кбит/сек
Аудио1: Русский AC3 5.1, 48 KHz, 384 kbps Аудио2: Английский DTS-HD Master Audio 5.1, 48 KHz, 24 bit, 3507 kbps, Lossless (DTS Core: 5.1, 48 KHz, 24bit, 1536 kbps) Аудио3: Английский AC3 2.0, 48 KHz, 192 kbps (комментарии Тима Роббинса и Кевина Бэйкона)
Русский дубляж (AC3 5.1 384 Кбит/сек), Русский профессиональный двухголосый (AC3 5.1 640 Кбит/сек), Английский оригинал (AC3 5.1 640 Кбит/сек)
Аудио1: Русский AC3 5.1, 48 KHz, 384 Kbps Аудио2: Английский DTS 5.1, 48 KHz, 1536 Kbps
Аудио: Rus: DTS 768 Kbps 6 channels Аудио: Eng: AC3 448 Kbps 6 channels Аудио: Fra: AC3 448 Kbps 6 channels
Sunet: Аудио 1: Русский, AC3, 48 kHz, 448 kbps Аудио 2: Английский, DTS, 48 kHz, 1536 Kbps
Русский (DTS, 6 ch, 1536 Кбит/с), aнглийский (DTS-HD, 6 ch, 3127 Кбит/с)
Sunet: Аудио1: Русский DTS 5.1, 48 KHz, 768 kbps профессиональный (полное дублирование) Аудио2: Русский DTS 5.1, 48 KHz, 1536 kbps авторский одноголосый (Юрий Сербин) отдельным файлом Аудио3: Английский DTS 5.1, 48 KHz, 1536 kbps
*/
	'sound'=>array('branch',26),
	'language'=>array(
		'map', array(
			'28'=>'1', // Ru
			'27'=>'3', // Ro
			'180'=>'2', // En
			'181'=>'4', // Germana
			'182'=>'5', // Français
			'183'=>'6', // Español
			'184'=>'7', // Japanese
			'185'=>'8', // Italiano
			'186'=>'9', // Portuguese
			'188'=>'10', // Other
			'187'=>'11', // Fără cuvinte
		)
	),
	'language_type'=>array(
		'map',array(
			'303'=>'1',  //Original (fără traducere)
			'304'=>'5',  //Amator (o voce)
			'305'=>'7',  //Amator (două voci)
			'306'=>'4',  //Amator (mai multe voci)
			'307'=>'8',  //Profesionistă (o voce)
			'308'=>'9',  //Profesionistă (două voci)
			'309'=>'3',  //Profesionistă (mai multe voci, voi)
			'310'=>'2',  //Profesionistă (dublată)
			'311'=>'6',  //Traducere sincronizată

			//Amator
			'697'=>'4,5,7',  //
			//Profesionistă
			'698'=>'2,3,8,9',  //
		)
	),
	'movie_genres_list_ids'=>	array(
		'map', array(
					"63"=>"1", //Action
					"82"=>"20", //Animation
					"64"=>"2", //Adventure
					"65"=>"3", //Biography
					"66"=>"4", //Comedy
					"67"=>"5", //Crime
					"68"=>"6", //Drama
					"81"=>"19", //Detectiv
					"83"=>"21", //Documentary
					"69"=>"7", //Family
					"70"=>"8", //Fantasy
					"71"=>"9", //History
					"72"=>"10", //Horror
					"80"=>"18", //Mystical
					"73"=>"11", //Music
					"74"=>"12", //Romance
					"75"=>"13", //Sci-Fi
					"76"=>"14", //Sport
					"77"=>"15", //Thriller
					"78"=>"16", //War
					"79"=>"17"  //Western
				),
	),
	'movie_quality'=> array(
		'map', array(
			"103" => "1", // DVDRip
			"105" => "2", // CAM
			"106" => "3", // TELESYNC (TS)
			"107" => "4", // TELECINE (TC)
			"108" => "5", // SCREENER (SCR)
			"110" => "6", // TVRip
			"104" => "7", // DVDscr
			"109" => "8", // SATRip
			"111" => "9", // HDTV
			"128" => "10", // HDTVRip
			"120" => "11", // BDRip
			"114" => "12", // Workprint
			"705" => "13", // WEB-DL
			"706" => "14", // WEBRip
		)
	),
	'hdtv_quality'=> array(
		'map', array(
			"120" => "1", // BDRip 720p
			"121" => "2", // BDRip 1080p
			"122" => "3,7", // BDRemux, // HDDVDRemux
			"123" => "4", // Blu-Ray
			"124" => "5", // HDDVDRip 720p
			"125" => "6", // HDDVDRip 1080p
			"127" => "8", // HDDVD
			"128" => "9", // HDTVRip
			"705" => "10", // WEB-DL
			"706" => "11", // WEBRip
		)
	),
	'category'=> array(
		'map', array(
			"89" => "1,18,12",  // Movies, // HDTV, // DVD
			"90" => "2", // Music
			"92" => "3", // Appz
			"93" => "4", // Games
			"94" => "5", // TV
			"312" => "7", // Other
			"95" => "8", // Books
			"96" => "9", // Music Video
			"97" => "10", // Anime
			"98" => "11", // Animation
			"313" => "13", // Movies Documentary
			"99" => "14", // Books Audio
			"100" => "15", // Video Lessons
			"101" => "16", // Photos
			"314" => "17", // Sport

			"115" => "12", // DVD
			"111" => "18", // HDTV -  după idee nu trebuie, dar să fim convinși
		)
	),
	'anime_genre'=> array(
		'map', array(
			"318" => "1", // Live Action
			"319" => "2", // Manga
			"320" => "3", // Movie
			"321" => "4", // OVA
			"324" => "5", // Series
			"322" => "6", // TV Anime
			"323" => "50", // Other
		)
	),
	'appz_license'=> array(
		'map', array(
			"328" => "1", // Shareware
			"329" => "2", // Freeware
			"330" => "3", // Open source
			"331" => "4", // Other
		)
	),
	'appz_os'=> array(
		'map', array(
			"145" => "1", // Windows
			"146" => "2", // Unix*
			"325" => "3", // Cross-Platform
			"150" => "4", // Mobiles*
			"326" => "5", // Other
		)
	),
	'music_genre'=> array(
		'map', array(
			"347" => "1", // A Capela
			"348" => "2", // Acid
			"349" => "3", // Acid Jazz
			"350" => "4", // Acid Punk
			"351" => "5", // Acoustic
			"352" => "6", // Alternative
			"353" => "7", // AlternRock
			"354" => "8", // Ambient
			"355" => "9", // Avantgarde
			"356" => "10", // Ballad
			"357" => "11", // Bass
			"358" => "12", // Bebob
			"359" => "13", // Big Band
			"360" => "143", // Black Metal
			"361" => "14", // Bluegrass
			"362" => "15", // Blues
			"363" => "16", // Booty Brass
			"364" => "17", // Cabaret
			"365" => "18", // Celtic
			"366" => "19", // Chamber Music
			"367" => "20", // Chanson
			"368" => "142", // Chill Out
			"369" => "21", // Chorus
			"370" => "22", // Christian Rap
			"371" => "23", // Classic Rock
			"372" => "24", // Classical
			"373" => "25", // Club
			"374" => "26", // Comedy
			"375" => "27", // Country
			"376" => "28", // Cult
			"377" => "29", // Dance
			"378" => "30", // Dance Hall
			"379" => "31", // Darkwave
			"380" => "32", // Death Metal
			"381" => "135", // Deathcore
			"382" => "33", // Disco
			"383" => "127", // Doom
			"384" => "134", // Downtempo
			"385" => "34", // Dream
			"386" => "133", // Drum&Bass
			"387" => "35", // Drum Solo
			"388" => "36", // Duet
			"389" => "37", // Easy Listening
			"390" => "38", // Electronic
			"391" => "128", // Emo
			"392" => "137", // Emocore
			"393" => "39", // Ethnic
			"394" => "40", // Euro-House
			"395" => "41", // Euro-Techno
			"396" => "42", // Eurodance
			"397" => "43", // Fast Fusion
			"398" => "44", // Folk
			"399" => "45", // Folk-Rock
			"400" => "46", // Folklore
			"401" => "47", // Freestyle
			"402" => "48", // Funk
			"403" => "49", // Fusion
			"404" => "50", // Game
			"405" => "51", // Gangsta
			"406" => "52", // Gospel
			"407" => "53", // Gothic
			"408" => "54", // Gothic Rock
			"409" => "139", // Grindcore
			"410" => "55", // Grunge
			"411" => "132", // Hardcore
			"412" => "56", // Hard Rock
			"413" => "57", // Hip-Hop
			"414" => "58", // House
			"415" => "59", // Humour
			"416" => "60", // Industrial
			"417" => "61", // Instrumental
			"418" => "62", // Instrumental Pop
			"419" => "63", // Instrumental Rock
			"420" => "64", // Jazz
			"421" => "65", // Jazz+Funk
			"422" => "66", // Jungle
			"423" => "67", // Latin
			"424" => "68", // Lo-Fi
			"425" => "69", // Meditative
			"426" => "70", // Metal
			"427" => "130", // Metalcore
			"428" => "138", // Mathcore
			"429" => "141", // Minimal
			"430" => "71", // Musical
			"431" => "72", // National Folk
			"432" => "73", // Native American
			"433" => "74", // New Age
			"434" => "75", // New Wave
			"435" => "76", // Noise
			"436" => "77", // Oldies
			"437" => "78", // Opera
			"438" => "80", // Polka
			"439" => "81", // Pop
			"440" => "82", // Pop-Folk
			"441" => "83", // Pop/Funk
			"442" => "84", // Porn Groove
			"443" => "136", // Post-Hardcore
			"444" => "85", // Poweer Ballad
			"445" => "140", // Powerviolence
			"446" => "86", // Pranks
			"447" => "87", // Primus
			"448" => "88", // Progressive Rock
			"449" => "89", // Psychedelic
			"450" => "90", // Psychedelic Rock
			"451" => "91", // Punk
			"452" => "92", // Punk Rock
			"453" => "93", // R&B
			"454" => "94", // Rap
			"455" => "95", // Rave
			"456" => "96", // Reggae
			"457" => "97", // Retro
			"458" => "98", // Revival
			"459" => "99", // Rhytmic Soul
			"460" => "100", // Rock
			"461" => "101", // Rock & Roll
			"462" => "102", // Samba
			"463" => "103", // Satire
			"464" => "129", // Screamo
			"465" => "104", // Showtunes
			"466" => "105", // Ska
			"467" => "106", // Slow Jam
			"468" => "107", // Slow Rock
			"469" => "108", // Sonata
			"470" => "109", // Soul
			"471" => "110", // Sound Clip
			"472" => "111", // Soundtrack
			"473" => "112", // Southern Rock
			"474" => "113", // Space
			"475" => "114", // Speech
			"476" => "115", // Swing
			"477" => "116", // Symphonic Rock
			"478" => "117", // Symphony
			"479" => "118", // Tango
			"480" => "119", // Techno
			"481" => "120", // Techno-Industrial
			"482" => "131", // Thrash
			"483" => "121", // Top 40
			"484" => "122", // Trailer
			"485" => "123", // Trance
			"486" => "124", // Tribal
			"487" => "125", // Trip-Hop
			"488" => "126", // Vocal
			"489" => "144", // Heavy Metal
			"490" => "145", // Power Metal
		)
	),
	'bookz_genre'=> array(
		'map', array(
			"332" => "1", // Biography
			"333" => "2", // Business & Money
			"334" => "3", // Children
			"335" => "4", // Computing & Internet
			"336" => "5", // Cooking, Food & Wine
			"337" => "6", // Diet & Health
			"338" => "7", // Education
			"339" => "8", // Fiction & Literature
			"340" => "9", // History
			"341" => "10", // Medicine
			"342" => "11", // Mystery & Crime
			"343" => "12", // Reference
			"344" => "13", // Religion
			"345" => "14", // Self-Improvement
			"505" => "15", // Science & Technics
			"506" => "100", // Other
		)
	),
	'games_genre'=> array(
		'map', array(
			'547' => '2', // First-Person Shooters(FPS)
			'581' => '3', // Role-Playing(RPG)
			'557' => '4', // Racing
			'552' => '5', // Real-Time Strategy(RTS)
			'549' => '6', // Other Shooters
			'550' => '7', // Tactical Shooters
			'582' => '8', // Platformers
			'583' => '9', // Fighting Games
			'584' => '10', // For Kids
			'560' => '11', // Adventure
			'553' => '12', // Turn by Turn Strategy
			'585' => '20', // Other

			//Action
			'546' => '1,2,6,7', // Action, First-Person Shooters(FPS), Other Shooters, Tactical Shooters
			//Simulation
			'555' => '13,4', // Simulation, Racing
			//Strategy
			'551' => '5,12', // Real-Time Strategy(RTS)
	)
	),
	'sport_genre'=> array(
		'map', array(
			'597' => '1', // Badminton
			'598' => '2', // Baseball
			'587' => '3', // Basketball
			'600' => '4', // Biathlon
			'601' => '5', // Billiards
			'602' => '6', // Board Sports
			'696' => '7', // Boat Racing
			'604' => '8', // Bobsledding
			'605' => '9', // Boomerang
			'606' => '10', // Bowling
			'607' => '11', // Boxball
			'588' => '12', // Boxing
			'608' => '13', // Bullfighting
			'609' => '14', // Buzkashi
			'610' => '15', // Camel Racing
			'611' => '16', // Canoe Polo
			'612' => '17', // Canoe-Kayak Racing
			'613' => '18', // Cheerleading
			'614' => '19', // Cockfighting
			'615' => '20', // Cricket
			'616' => '21', // Croquet
			'617' => '22', // Curling
			'599' => '23', // Cycling
			'618' => '24', // Danball
			'619' => '25', // Dirtsurfing
			'620' => '26', // Dodgeball
			'621' => '27', // Dog Racing
			'622' => '28', // Dogsledding
			'623' => '105', // Drifting
			'624' => '29', // Equestrian
			'625' => '30', // Extreme Sports
			'626' => '31', // Fencing
			'589' => '32', // Fighting
			'627' => '33', // Fishing
			'628' => '34', // Flying Discs
			'592' => '103', // Formula 1
			'629' => '35', // Footbag
			'591' => '36', // Football
			'630' => '37', // Freediving
			'631' => '38', // Golf
			'632' => '39', // Gymnastics
			'633' => '40', // Handball
			'634' => '41', // Hockey
			'635' => '42', // Horse Racing
			'636' => '43', // Hurling
			'637' => '44', // Jai-Alai
			'638' => '45', // Kabaddi
			'639' => '46', // Kickball
			'640' => '47', // Korfball
			'642' => '48', // Lacrosse
			'643' => '49', // Le Parkour
			'644' => '50', // Luge
			'645' => '51', // Lumbering
			'590' => '52', // Martial Arts
			'593' => '53', // Motorcycle Racing
			'647' => '54', // Mountainboarding
			'649' => '55', // Netball
			'650' => '56', // Orienteering
			'651' => '57', // Paddleball
			'652' => '58', // Paddling
			'653' => '59', // Paintball
			'654' => '60', // Pickleball
			'655' => '61', // Polo
			'656' => '62', // Racewalking
			'657' => '63', // Racquetball
			'658' => '64', // Ringette
			'659' => '65', // Rodeo
			'660' => '66', // Rowing
			'595' => '67', // Rugby
			'661' => '68', // Running
			'662' => '69', // Sailing
			'663' => '70', // Sandboarding
			'664' => '71', // Sepak Takraw
			'665' => '72', // Shinty
			'666' => '73', // Shooting
			'667' => '74', // Skateboarding
			'668' => '75', // Skating
			'669' => '76', // Skeleton
			'670' => '77', // Skiing
			'671' => '78', // Snowboarding
			'672' => '79', // Snowmobiling
			'673' => '80', // Soccer
			'674' => '81', // Softball
			'675' => '82', // Sports Entertainment
			'676' => '83', // Squash
			'677' => '84', // Surfing
			'678' => '85', // Swimming and Diving
			'679' => '86', // Table Tennis
			'680' => '87', // Tchoukball
			'594' => '88', // Tennis
			'681' => '89', // Track and Field
			'682' => '90', // Triathlon
			'683' => '91', // Tug-of-War
			'684' => '92', // Twirling
			'596' => '93', // Volleyball
			'685' => '94', // Wakeboarding
			'686' => '95', // Walking
			'687' => '96', // Water Polo
			'688' => '97', // Waterskiing
			'689' => '98', // Weightlifting
			'690' => '99', // Wheelchair Racing
			'691' => '100', // Windsurfing
			'692' => '101', // Winter Sports
			'694' => '104', // World Cup 10
			'693' => '102', // Wrestling
			'646' => '106', // MMA
			'641' => '107', // K-1
			'648' => '108', // Muay
		)
	)
);
$tagMap['music_format'] = array(
   // 'map', buildCategory2TagMap() ); //sa nu mai execute functia de fiecare data >)
	'map', array (
		'510' => '2,5', //
		'508' => '3', //
		'509' => '4', //
		'512' => '6', //
		'158' => '7', //
		'513' => '8', //
		'514' => '9', //
		'515' => '10', //
		'516' => '11', //
		'517' => '12', //
		'518' => '13', //
		'519' => '14', //
		'154' => '15,17', //
		'521' => '16' //
	)
);


$tagMap['torrentTitle'] = array('mix',
	array(
		array('branch',115), // DVD-5, DVD-9...
		array('branch',150), // Telefoane mobile
		array('branch',143)  // Console de joc
	)
);


function buildCategory2TagMap() {
  // If you want to modify somethink here, NOT, go music.html to do that can copy here you lazy programmer
  $music_format = '<option value="0">-</option>
       <option value="1" selected categtag="157">MP3</option>
       <option value="2" categtag="510">M4A</option>
       <option value="3" categtag="508">OGG</option>
       <option value="4" categtag="509">WMA</option>
       <option value="5" categtag="510">AAC</option>
       <option value="6" categtag="512">MPC</option>
       <option value="7" categtag="158">FLAC</option>
       <option value="8" categtag="513">APE</option>
       <option value="9" categtag="514">SHN</option>
       <option value="10" categtag="515">WV</option>
       <option value="11" categtag="516">OFR</option>
       <option value="12" categtag="517">WAV</option>
       <option value="13" categtag="518">AIFF</option>
       <option value="14" categtag="519">SPX</option>
       <option value="15" categtag="154">AA</option>
       <option value="16" categtag="521">AC3</option>
       <option value="17" categtag="154">(multiple)</option>';

  preg_match_all('/value="(.+?)" categtag="(.+?)"/', $music_format, $matches, PREG_SET_ORDER);

  $map = array();

  foreach ($matches as $val) {
    $category = $val[1];
    $categtag = $val[2];
    if (isset($map[$categtag]))
      $map[$categtag] = $map[$categtag] . ','.$category;
    else
      $map[$categtag] = $category;
  }

  return $map;
}