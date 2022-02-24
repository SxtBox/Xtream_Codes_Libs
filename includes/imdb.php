<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class IMDBException extends Exception
{
}
class IMDB
{
    public $strNotFound = "n/A";
    public $strSeperator = " / ";
    protected $_fCookie = false;
    protected $_strUrl = NULL;
    protected $_strSource = NULL;
    protected $_strId = false;
    public $isReady = false;
    protected $_strRoot = "";
    const IMDB_TIMEOUT = 15;
    const IMDB_LANG = "en-US, en";
    const IMDB_CHARSET = "utf-8,ISO-8859-1;q=0.5";
    const IMDB_SEARCHFOR = "all";
    const IMDB_AKA = "~Also Known As:</h4>(.*)<span~Ui";
    const IMDB_ASPECT_RATIO = "~Aspect Ratio:</h4>(.*)</div>~Ui";
    const IMDB_BUDGET = "~Budget:</h4>(.*)<span~Ui";
    const IMDB_CAST = "~itemprop=\"actor\"(?:.*)><a href=\"/name/nm(\\d+)/(?:.*)\"(?:\\s*)itemprop='url'>(?:\\s*)<span class=\"itemprop\" itemprop=\"name\">(.*)</span>~Ui";
    const IMDB_FULL_CAST = "~<span class=\"itemprop\" itemprop=\"name\">(.*?)</span>~Ui";
    const IMDB_CHAR = "~<td class=\"character\">(?:\\s*)<div>(.*)</div>(?:\\s*)</td~Ui";
    const IMDB_COLOR = "~href=\"/search/title\\?colors=(?:.*)\"(?:\\s*)itemprop='url'>(.*)</a>~Ui";
    const IMDB_COMPANY = "~Production Co:</h4>(.*)</div>~Ui";
    const IMDB_COMPANY_NAME = "~href=\"/company/co(\\d+)(?:\\?.*)\"(?:\\s*)itemprop='url'>(.*)</a>~Ui";
    const IMDB_COUNTRY = "~href=\"/country/(\\w+)\\?(?:.*)\"(?:\\s*)itemprop='url'>(.*)</a>~Ui";
    const IMDB_CREATOR = "~(?:Creator|Creators):</h4>(.*)</div>~Ui";
    const IMDB_DESCRIPTION = "~<p itemprop=\"description\">(.*)(?:<a|<\\/p>)~Ui";
    const IMDB_DIRECTOR = "~(?:Director|Directors):</h4>(.*)</div>~Ui";
    const IMDB_GENRE = "~href=\"/genre/(.*)(?:\\?.*)\"(?:\\s*)>(.*)</a>~Ui";
    const IMDB_ID = "~(tt\\d{6,})~";
    const IMDB_LANGUAGES = "~href=\"/language/(.*)(?:\\?.*)\"(?:\\s*)itemprop='url'>(.*)</a>~Ui";
    const IMDB_LOCATION = "~href=\"/search/title\\?locations=(.*)(?:&.*)\"itemprop='url'>(.*)</a>~Ui";
    const IMDB_MPAA = "~span itemprop=\"contentRating\"(?:.*)>(.*)</span~Ui";
    const IMDB_NAME = "~href=\"/name/nm(\\d+)/(?:.*)\"(?:\\s*)itemprop='(?:\\w+)'><span class=\"itemprop\" itemprop=\"name\">(.*)</span>~Ui";
    const IMDB_OPENING = "~Opening Weekend:</h4>(.*)\\(~Ui";
    const IMDB_PLOT = "~Storyline</h2>(?:\\s*)<div class=\"inline canwrap\" itemprop=\"description\">(?:\\s*)<p>(.*)(?:<em|<\\/p>|<\\/div>)~Ui";
    const IMDB_POSTER = "~\"src=\"(.*)\"itemprop=\"image\" \\/>~Ui";
    const IMDB_RATING = "~<span itemprop=\"ratingValue\">(.*)</span>~Ui";
    const IMDB_REDIRECT = "~Location:(?:\\s*)(.*)~";
    const IMDB_RELEASE_DATE = "~Release Date:</h4>(.*)(?:<span|<\\/div>)~Ui";
    const IMDB_RUNTIME = "~<time itemprop=\"duration\" datetime=\"(?:.*)\"(?:\\s*)>(?:\\s*)(.*)</time>~Uis";
    const IMDB_SEARCH = "~<td class=\"result_text\"> <a href=\"\\/title\\/tt(\\d+)\\/(?:.*)\"(?:\\s*)>(.*)<\\/a>~Uis";
    const IMDB_SEASONS = "~Season:</h4>(?:\\s*)<span class=\"see-more inline\">(.*)</span>(?:\\s*)</div>~Ui";
    const IMDB_SITES = "~Official Sites:</h4>(.*)(?:<a href=\"officialsites|</div>)~Ui";
    const IMDB_SITES_A = "~href=\"(.*)\" itemprop='url'>(.*)</a>~Ui";
    const IMDB_SOUND_MIX = "~Sound Mix:</h4>(.*)</div>~Ui";
    const IMDB_SOUND_MIX_A = "~href=\"/search/title\\?sound_mixes=(?:.*)\"(?:\\s*)itemprop='url'>(.*)</a>~Ui";
    const IMDB_TAGLINE = "~Taglines:</h4>(.*)(?:<span|<\\/span>|</div>)~Ui";
    const IMDB_TITLE = "~property='og:title' content=\"(.*)(?:\\s*)\\((?:.*)\\)\"~Ui";
    const IMDB_TITLE_ORIG = "~<span class=\"title-extra\" itemprop=\"name\">(.*)<i>\\(original title\\)<\\/i>(?:\\s*)</span>~Ui";
    const IMDB_TRAILER = "~href=\"/video/(.*)/(?:\\?.*)\"(?:.*)itemprop=\"trailer\">~Ui";
    const IMDB_URL = "~http://(?:.*\\.|.*)imdb.com/(?:t|T)itle(?:\\?|/)(..\\d+)~i";
    const IMDB_VOTES = "~<span itemprop=\"ratingCount\">(.*)</span>~Ui";
    const IMDB_YEAR = "~<h1 class=\"header\">(?:\\s*)<span class=\"itemprop\" itemprop=\"name\">(?:.*)</span>(?:\\s*)<span class=\"nobr\">\\((.*)\\)</span>~Ui";
    const IMDB_WRITER = "~(?:Writer|Writers):</h4>(.*)</div>~Ui";
    const IMDB_VERSION = "5.5.20";
    public function __construct($strSearch)
    {
        if (!$this->_strRoot) {
            $this->_strRoot = dirname(__FILE__);
        }
        if (function_exists("curl_init")) {
            IMDB::fetchUrl($strSearch);
        } else {
            throw new IMDBException("You need PHP with cURL enabled to use this script!");
        }
    }
    protected function matchRegex($strContent, $strRegex, $intIndex = NULL)
    {
        $arrMatches = false;
        preg_match_all($strRegex, $strContent, $arrMatches);
        if ($arrMatches !== false) {
            if (!($intIndex != NULL && is_int($intIndex))) {
                return $arrMatches;
            }
            if (!$arrMatches[$intIndex]) {
                return false;
            }
            return $arrMatches[$intIndex][0];
        }
        return false;
    }
    public function getShortText($strText, $intLength = 100)
    {
        $strText = trim($strText) . " ";
        $strText = substr($strText, 0, $intLength);
        $strText = substr($strText, 0, strrpos($strText, " "));
        return $strText . "�";
    }
    protected function fetchUrl($strSearch)
    {
        $strSearch = trim($strSearch);
        if ($strSearch == "##REMOTEDEBUG##") {
            $strSearch = "http://www.imdb.com/title/tt1022603/";
            echo "<pre>Running PHP-IMDB-Grabber v" . IMDB::IMDB_VERSION . ".</pre>";
        }
        $strId = IMDB::matchRegex($strSearch, IMDB::IMDB_URL, 1);
        if (!$strId) {
            $strId = IMDB::matchRegex($strSearch, IMDB::IMDB_ID, 1);
        }
        if ($strId) {
            $this->_strId = preg_replace("~[\\D]~", "", $strId);
            $this->_strUrl = "http://www.imdb.com/title/tt" . $this->_strId . "/";
            $bolFound = false;
            $this->isReady = true;
        } else {
            $strSearchFor = "all";
            if (strtolower(IMDB::IMDB_SEARCHFOR) == "movie") {
                $strSearchFor = "tt&ttype=ft&ref_=fn_ft";
            } else {
                if (strtolower(IMDB::IMDB_SEARCHFOR) == "tvtitle") {
                    $strSearchFor = "tt&ttype=tv&ref_=fn_tv";
                } else {
                    if (strtolower(IMDB::IMDB_SEARCHFOR) == "tvepisode") {
                        $strSearchFor = "tt&ttype=ep&ref_=fn_ep";
                    }
                }
            }
            $this->_strUrl = "http://www.imdb.com/find?s=" . $strSearchFor . "&q=" . str_replace(" ", "+", $strSearch);
            $bolFound = true;
        }
        if (function_exists("sys_get_temp_dir")) {
            $this->_fCookie = tempnam(sys_get_temp_dir(), "imdb");
        }
        $arrInfo = $this->doCurl($this->_strUrl);
        $strOutput = $arrInfo["contents"];
        if ($strOutput !== false) {
            if ($strMatch = $this->matchRegex($strOutput, IMDB::IMDB_REDIRECT, 1)) {
                $arrExplode = explode("?fr=", $strMatch);
                $strMatch = $arrExplode[0] ? $arrExplode[0] : $strMatch;
                $this->isReady = false;
                IMDB::fetchUrl($strMatch);
                return true;
            }
            if ($strMatch = $this->matchRegex($strOutput, IMDB::IMDB_SEARCH, 1)) {
                $strMatch = "http://www.imdb.com/title/tt" . $strMatch . "/";
                $this->_strSource = NULL;
                $this->isReady = false;
                IMDB::fetchUrl($strMatch);
                return true;
            }
            if ($arrInfo["http_code"] != 200 && $arrInfo["http_code"] != 302) {
                return false;
            }
            $this->_strSource = $strOutput;
            $this->_strSource = preg_replace("~(\\r|\\n|\\r\\n)~", "", $this->_strSource);
            return true;
        }
        $this->_strSource = file_get_contents($fCache);
        if (!$this->_strSource) {
            return false;
        }
        return true;
    }
    protected function doCurl($strUrl, $bolOverWriteSource = true)
    {
        $oCurl = curl_init($strUrl);
        curl_setopt_array($oCurl, array(CURLOPT_VERBOSE => false, CURLOPT_HEADER => true, CURLOPT_HTTPHEADER => array("Accept-Language:" . IMDB::IMDB_LANG . ";q=0.5", "Accept-Charset:" . IMDB::IMDB_CHARSET), CURLOPT_FRESH_CONNECT => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => IMDB::IMDB_TIMEOUT, CURLOPT_CONNECTTIMEOUT => 0, CURLOPT_REFERER => "http://www.google.com", CURLOPT_USERAGENT => "Googlebot/2.1 (+http://www.google.com/bot.html)", CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => $this->_fCookie));
        $strOutput = curl_exec($oCurl);
        if ($this->_fCookie) {
            @unlink($this->_fCookie);
        }
        $arrInfo = curl_getinfo($oCurl);
        curl_close($oCurl);
        $arrInfo["contents"] = $strOutput;
        if ($bolOverWriteSource) {
            $this->_strSource = $strOutput;
        }
        if (!($arrInfo["http_code"] != 200 && $arrInfo["http_code"] != 302)) {
            return $arrInfo;
        }
        return false;
    }
    protected function saveImage($_strUrl)
    {
        $_strUrl = trim($_strUrl);
        if (!(preg_match("/imdb-share-logo.gif/", $_strUrl) && file_exists("posters/not-found.jpg"))) {
            $strFilename = MOVIES_IMAGES . $this->_strId . ".jpg";
            if (!file_exists($strFilename)) {
                $oCurl = curl_init($_strUrl);
                curl_setopt_array($oCurl, array(CURLOPT_VERBOSE => false, CURLOPT_HEADER => false, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => IMDB::IMDB_TIMEOUT, CURLOPT_CONNECTTIMEOUT => 0, CURLOPT_REFERER => $_strUrl, CURLOPT_BINARYTRANSFER => true));
                $sOutput = curl_exec($oCurl);
                $arrInfo = curl_getinfo($oCurl);
                curl_close($oCurl);
                if (!($arrInfo["http_code"] != 200 && $arrInfo["http_code"] != 302)) {
                    $oFile = fopen($strFilename, "x");
                    fwrite($oFile, $sOutput);
                    fclose($oFile);
                    return $this->_strId . ".jpg";
                }
                return $_strUrl;
            }
            return $this->_strId . ".jpg";
        }
        return false;
    }
    public function getAka()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_AKA, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getAspectRatio()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_ASPECT_RATIO, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getBudget()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_BUDGET, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCast($intLimit = 20, $bolMore = true)
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_CAST);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    if ($intLimit > $i) {
                        $arrReturn[] = trim($strName);
                    } else {
                        break;
                    }
                }
                return implode($this->strSeperator, $arrReturn) . ($bolMore && $intLimit < count($arrReturned[2]) ? "�" : "");
            }
        }
        return $this->strNotFound;
    }
    public function getFullCast($intLimit = false)
    {
        if ($this->isReady) {
            $fullCastUrl = sprintf("http://www.imdb.com/title/tt%s/fullcredits", $this->_strId);
            $arrInfo = $this->doCurl($fullCastUrl, false);
            if ($arrInfo) {
                $arrReturned = $this->matchRegex($arrInfo["contents"], IMDB::IMDB_FULL_CAST);
                if (!count($arrReturned[1])) {
                } else {
                    foreach ($arrReturned[1] as $i => $strName) {
                        if (!($intLimit !== false && $intLimit <= $i)) {
                            $arrReturn[] = trim($strName);
                        } else {
                            break;
                        }
                    }
                    @file_put_contents($fCache, @serialize($arrReturn));
                    return implode($this->strSeperator, $arrReturn);
                }
            } else {
                return $this->strNotFound;
            }
        }
        return $this->strNotFound;
    }
    public function getCastAsUrl($intLimit = 20, $bolMore = true, $strTarget = "")
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_CAST);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    if ($intLimit > $i) {
                        $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                    } else {
                        break;
                    }
                }
                return implode($this->strSeperator, $arrReturn) . ($bolMore && $intLimit < count($arrReturned[2]) ? "�" : "");
            }
        }
        return $this->strNotFound;
    }
    public function getCastAndCharacter($intLimit = 20, $bolMore = true)
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_CAST);
            $arrChar = $this->matchRegex($this->_strSource, IMDB::IMDB_CHAR);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    if ($intLimit > $i) {
                        $arrChar[1][$i] = trim(preg_replace("~\\((.*)\\)~Ui", "", strip_tags($arrChar[1][$i])));
                        if ($arrChar[1][$i]) {
                            $arrReturn[] = trim($strName) . " as " . trim($arrChar[1][$i]);
                        } else {
                            $arrReturn[] = trim($strName);
                        }
                    } else {
                        break;
                    }
                }
                return implode($this->strSeperator, $arrReturn) . ($bolMore && $intLimit < count($arrReturned[2]) ? "�" : "");
            }
        }
        return $this->strNotFound;
    }
    public function getCastAndCharacterAsUrl($intLimit = 20, $bolMore = true, $strTarget = "")
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_CAST);
            $arrChar = $this->matchRegex($this->_strSource, IMDB::IMDB_CHAR);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    if ($intLimit > $i) {
                        $arrChar[1][$i] = trim(preg_replace("~\\((.*)\\)~Ui", "", $arrChar[1][$i]));
                        preg_match_all("~<a href=\"/character/ch(\\d+)/\">(.*)</a>~Ui", $arrChar[1][$i], $arrMatches);
                        if (isset($arrMatches[1][0]) && isset($arrMatches[2][0])) {
                            $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a> as <a href=\"http://www.imdb.com/character/ch" . trim($arrMatches[1][0]) . "/\">" . trim($arrMatches[2][0]) . "</a>";
                        } else {
                            if ($arrChar[1][$i]) {
                                $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a> as " . strip_tags(trim($arrChar[1][$i]));
                            } else {
                                $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                            }
                        }
                    } else {
                        break;
                    }
                }
                return implode($this->strSeperator, $arrReturn) . ($bolMore && $intLimit < count($arrReturned[2]) ? "�" : "");
            }
        }
        return $this->strNotFound;
    }
    public function getColor()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_COLOR, 1))) {
            } else {
                return $strReturn;
            }
        }
        return $this->strNotFound;
    }
    public function getCompany()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_COMPANY, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_COMPANY_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCompanyAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_COMPANY, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_COMPANY_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/company/co" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCountry()
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_COUNTRY);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCountryAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_COUNTRY);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/country/" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCreator()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_CREATOR, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getCreatorAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_CREATOR, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getDescription()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_DESCRIPTION, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getDirector()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_DIRECTOR, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getDirectorAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_DIRECTOR, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getGenre()
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_GENRE);
            if (!count($arrReturned[1])) {
            } else {
                foreach ($arrReturned[1] as $strName) {
                    if ($strName) {
                        $arrReturn[] = trim($strName);
                    }
                }
                return implode($this->strSeperator, array_unique($arrReturn));
            }
        }
        return $this->strNotFound;
    }
    public function getGenreAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_GENRE);
            if (!count($arrReturned[1])) {
            } else {
                foreach ($arrReturned[1] as $i => $strName) {
                    if ($strName) {
                        $arrReturn[] = "<a href=\"http://www.imdb.com/genre/" . trim($strName) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                    }
                }
                return implode($this->strSeperator, array_unique($arrReturn));
            }
        }
        return $this->strNotFound;
    }
    public function getLanguages()
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_LANGUAGES);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getLanguagesAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $arrReturned = $this->matchRegex($this->_strSource, IMDB::IMDB_LANGUAGES);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/language/" . trim($arrReturned[1][$i]) . "\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getLocation()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_LOCATION, 2))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getLocationAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_LOCATION, 2))) {
            } else {
                return "<a href=\"http://www.imdb.com/search/title?locations=" . urlencode(trim($strReturn)) . "\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strReturn) . "</a>";
            }
        }
        return $this->strNotFound;
    }
    public function getOpening()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_OPENING, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getMpaa()
    {
        if ($this->isReady) {
            $strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_MPAA);
            if (!($strReturn && isset($strReturn[1]) && isset($strReturn[1][0]))) {
            } else {
                return trim($strReturn[1][0]);
            }
        }
        return $this->strNotFound;
    }
    public function getPlot($intLimit = 0)
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_PLOT, 1))) {
            } else {
                if (!$intLimit) {
                    return trim(strip_tags($strReturn));
                }
                return strip_tags($this->getShortText($strReturn, $intLimit));
            }
        }
        return $this->strNotFound;
    }
    public function getPoster($sSize = "small")
    {
        if (!$this->isReady) {
        } else {
            $strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_POSTER, 1);
            if ($strReturn) {
                if (strtolower($sSize) == "big") {
                    $strReturn = substr($strReturn, 0, strpos($strReturn, "_"));
                }
                $strLocal = $this->saveImage($strReturn);
                if ($strLocal) {
                    return $strLocal;
                }
                return $this->strNotFound;
            }
            return "not-found.jpg";
        }
    }
    public function getRating()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_RATING, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getReleaseDate()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_RELEASE_DATE, 1))) {
            } else {
                return str_replace("(", " (", trim($strReturn));
            }
        }
        return $this->strNotFound;
    }
    public function getRuntime()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_RUNTIME, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getSeasons()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_SEASONS)) {
                $strReturn = strip_tags(implode($strReturn[1]));
                $strFind = array("&raquo;", "&nbsp;", "Full episode list", " ");
                $strReturn = str_replace($strFind, "", $strReturn);
                $arrReturn = explode("|", $strReturn);
                if (!$arrReturn[0]) {
                } else {
                    return implode($this->strSeperator, array_reverse($arrReturn));
                }
            }
        }
        return $this->strNotFound;
    }
    public function getSeasonsAsUrl()
    {
        if ($this->isReady) {
            if ($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_SEASONS)) {
                $strReturn = strip_tags(implode($strReturn[1]));
                $strFind = array("&raquo;", "&nbsp;", "Full episode list", " ");
                $strReturn = str_replace($strFind, "", $strReturn);
                $arrSeasons = explode("|", $strReturn);
                if (!$arrSeasons[0]) {
                } else {
                    foreach (array_reverse($arrSeasons) as $sSeasons) {
                        $arrReturn[] = "<a href=\"http://www.imdb.com/title/tt" . $this->_strId . "/episodes?season=" . $sSeasons . "\">" . $sSeasons . "</a>";
                    }
                    return implode($this->strSeperator, $arrReturn);
                }
            }
        }
        return $this->strNotFound;
    }
    public function getSitesAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_SITES, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_SITES_A);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    if (strtolower(substr($arrReturned[1][$i], 0, 9)) == "/offsite/") {
                        $arrReturn[] = "<a href=\"http://www.imdb.com" . $arrReturned[1][$i] . "\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                    }
                }
                return $arrReturn != NULL ? implode($this->strSeperator, $arrReturn) : $this->strNotFound;
            }
        }
        return $this->strNotFound;
    }
    public function getSoundMix()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_SOUND_MIX, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_SOUND_MIX_A);
            if (!count($arrReturned[1])) {
            } else {
                foreach ($arrReturned[1] as $i => $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getTagline()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_TAGLINE, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getTitle($bForceLocal = false)
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, $bForceLocal ? IMDB::IMDB_TITLE : IMDB::IMDB_TITLE_ORIG, 1))) {
                if (!($strReturn = $this->matchRegex($this->_strSource, $bForceLocal ? IMDB::IMDB_TITLE_ORIG : IMDB::IMDB_TITLE, 1))) {
                } else {
                    return trim($strReturn);
                }
            } else {
                return ltrim(rtrim(trim($strReturn), "\""), "\"");
            }
        }
        return $this->strNotFound;
    }
    public function getTrailerAsUrl()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_TRAILER, 1))) {
            } else {
                return "http://www.imdb.com/video/" . $strReturn . "/player";
            }
        }
        return $this->strNotFound;
    }
    public function getUrl()
    {
        if (!$this->isReady) {
            return $this->strNotFound;
        }
        return $this->_strUrl;
    }
    public function getVotes()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_VOTES, 1))) {
            } else {
                return trim($strReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getWriter()
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_WRITER, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = trim($strName);
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getWriterAsUrl($strTarget = "")
    {
        if ($this->isReady) {
            $strContainer = $this->matchRegex($this->_strSource, IMDB::IMDB_WRITER, 1);
            $arrReturned = $this->matchRegex($strContainer, IMDB::IMDB_NAME);
            if (!count($arrReturned[2])) {
            } else {
                foreach ($arrReturned[2] as $i => $strName) {
                    $arrReturn[] = "<a href=\"http://www.imdb.com/name/nm" . trim($arrReturned[1][$i]) . "/\"" . ($strTarget ? " target=\"" . $strTarget . "\"" : "") . ">" . trim($strName) . "</a>";
                }
                return implode($this->strSeperator, $arrReturn);
            }
        }
        return $this->strNotFound;
    }
    public function getYear()
    {
        if ($this->isReady) {
            if (!($strReturn = $this->matchRegex($this->_strSource, IMDB::IMDB_YEAR, 1))) {
            } else {
                return substr(preg_replace("~[\\D]~", "", $strReturn), 0, 4);
            }
        }
        return $this->strNotFound;
    }

// BUILD JSON DATA
    public function getAll()
    {
        $oData = new stdClass();
        $oData->aka = $this->getAka();
        $oData->aspectRatio = $this->getAspectRatio();
        $oData->budget = $this->getBudget();
        $oData->cast = $this->getCast();
        $oData->fullCast = $this->getFullCast();
        $oData->castAsUrl = $this->getCastAsUrl();
        $oData->castAndCharacter = $this->getCastAndCharacter();
        $oData->castAndCharacterAsUrl = $this->getCastAndCharacterAsUrl();
        $oData->color = $this->getColor();
        $oData->company = $this->getCompany();
        $oData->companyAsUrl = $this->getCompanyAsUrl();
        $oData->country = $this->getCountry();
        $oData->countryAsUrl = $this->getCountryAsUrl();
        $oData->creator = $this->getCreator();
        $oData->creatorAsUrl = $this->getCreatorAsUrl();
        $oData->description = $this->getDescription();
        $oData->director = $this->getDirector();
        $oData->directorAsUrl = $this->getDirectorAsUrl();
        $oData->genre = $this->getGenre();
        $oData->genreAsUrl = $this->getGenreAsUrl();
        $oData->languages = $this->getLanguages();
        $oData->languagesAsUrl = $this->getLanguagesAsUrl();
        $oData->location = $this->getLocation();
        $oData->locationAsUrl = $this->getLocationAsUrl();
        $oData->mpaa = $this->getMpaa();
        $oData->opening = $this->getOpening();
        $oData->plot = $this->getPlot();
        $oData->poster = $this->getPoster();
        $oData->rating = $this->getRating();
        $oData->releaseDate = $this->getReleaseDate();
        $oData->runtime = $this->getRuntime();
        $oData->seasons = $this->getSeasons();
        $oData->seasonsAsUrl = $this->getSeasonsAsUrl();
        $oData->soundMix = $this->getSoundMix();
        $oData->sitesAsUrl = $this->getSitesAsUrl();
        $oData->tagline = $this->getTagline();
        $oData->title = $this->getTitle();
        $oData->trailerAsUrl = $this->getTrailerAsUrl();
        $oData->url = $this->getUrl();
        $oData->votes = $this->getVotes();
        $oData->writers = $this->getWriter();
        $oData->writersAsUrl = $this->getWriterAsUrl();
        $oData->year = $this->getYear();
        return $oData;
    }
}

?>