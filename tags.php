<?php
$text = 'TY  - Type of reference (must be the first tag)
A2  - Secondary Author (each author on its own line preceded by the tag)
A3  - Tertiary Author (each author on its own line preceded by the tag)
A4  - Subsidiary Author (each author on its own line preceded by the tag)
AB  - Abstract
AD  - Author Address
AN  - Accession Number
AU  - Author (each author on its own line preceded by the tag)
C1  - Custom 1
C2  - Custom 2
C3  - Custom 3
C4  - Custom 4
C5  - Custom 5
C6  - Custom 6
C7  - Custom 7
C8  - Custom 8
CA  - Caption
CN  - Call Number
CY  - Place Published
DA  - Date
DB  - Name of Database
DO  - DOI
DP  - Database Provider
EP  - End Page
ET  - Edition
IS  - Number
J2  - Alternate Title (this field is used for the abbreviated title of a book or journal name)
KW  - Keywords (keywords should be entered each on its own line preceded by the tag)
L1  - File Attachments (this is a link to a local file on the users system not a URL link)
L4  - Figure (this is also meant to be a link to a local file on the userss system and not a URL link)
LA  - Language
LB  - Label
M1  - Number
M3  - Type of Work
N1  - Notes
NV  - Number of Volumes
OP  - Original Publication
PB  - Publisher
PY  - Year
RI  - Reviewed Item
RN  - Research Notes
RP  - Reprint Edition
SE  - Section
SN  - ISBN/ISSN
SP  - Start Page
ST  - Short Title
T2  - Secondary Title
T3  - Tertiary Title
TA  - Translated Author
TI  - Title
TT  - Translated Title
UR  - URL
VL  - Volume
Y2  - Access Date
ER  - End of Reference (must be the last tag)
TY - 	Type of reference (must be the first tag) 	Typ der Referenz (z.B. JOUR = Zeitschrift)
ID - 	Reference ID (not imported to reference software)
T1 - 	Primary title
TI - 	Book title
CT - 	Title of unpublished reference
A1 - 	Primary author
A2 - 	Secondary author (each name on separate line)
AU - 	Author (syntax. Last name, First name, Suffix)
Y1 - 	Primary date
PY - 	Publication year (YYYY/MM/DD)
N1 - 	Notes
KW - 	Keywords (each keyword must be on separate line preceded KW -)
RP - 	Reprint status (IN FILE, NOT IN FILE, ON REQUEST (MM/DD/YY))
SP - 	Start page number
EP - 	Ending page number
JF - 	Periodical full name
JO - 	Periodical standard abbreviation
JA - 	Periodical in which article was published
J1 - 	Periodical name - User abbreviation 1
J2 - 	Periodical name - User abbreviation 2
VL - 	Volume number
IS - 	Issue number
T2 - 	Title secondary
CY - 	City of Publication
PB - 	Publisher
U1 - 	User definable 1
U5 - 	User definable 5
T3 - 	Title series
N2 - 	Abstract
SN - 	ISSN/ISBN (e.g. ISSN XXXX-XXXX)
AV - 	Availability
M1 - 	Misc. 1
M3 - 	Misc. 3
AD - 	Address
UR - 	Web/URL
L1 - 	Link to PDF
L2 - 	Link to Full-text
L3 - 	Related records
L4 - 	Images
ER - 	End of Reference (must be the last tag) 	Ende der Referenz (muß das letzte Tag sein)';
$tags = array();
preg_match_all('~([A-Z0-9]{2})\s+\-\s+(.*)~', $text, $tags, PREG_SET_ORDER);
foreach($tags as $tag)
	$a[$tag[1]] = $tag[2];
ksort($a);
foreach($a as $tag => $desc){
	echo $tag.' - '.$desc.'<br />';
}