<?php

/**
 * @author Ben Keen <ben.keen@gmail.com>, origin code Zeeshan Shaikh
 * @package DataTypes
 */
class DataType_PAN extends DataTypePlugin {

	protected $isEnabled = true;
	protected $dataTypeName = "PAN";
	protected $dataTypeFieldGroup = "credit_card_data";
	protected $dataTypeFieldGroupOrder = 10;
	protected $jsModules = array("PAN.js");

	private $data = array(
		array(
			"cards" => array("mastercard", "discover", "visa_electron"),
			"length" => "16",
			"formats" => array(
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX"
			)
		),
		array(
			"cards" => array("visa"),
			"length" => "13,16",
			"formats" => array(
				"XXXXXXXXXXXXX",
				"XXXX XXX XX XXXX",
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX"
			)
		),
		array(
			"cards" => array("amex", "enroute"),
			"length" => "15",
			"formats" => array(
				"XXXXXXXXXXXXXXX",
				"XXXX XXXXXX XXXXX"
			)
		),
		array(
			"cards" => array("carte_blanche", "diners_club_international"),
			"length" => "14",
			"formats" => array(
				"XXXXXXXXXXXXXXX",
				"XXXX XXXXXX XXXXX"
			)
		),
		array(
			"cards" => array("jcb"),
			"length" => "15,16",
			"formats" => array(
				"XXXXXXXXXXXXXXX",
				"XXXX XXXXXX XXXXX",
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX"
			)
		),
		array(
			"cards" => array("maestro"),
			"length" => "12-19",
			"formats" => array(
				"XXXXXXXXXXXX",
				"XXXXXXXXXXXXX",
				"XXXX XXX XX XXXX",
				"XXXXXXXXXXXXXX",
				"XXXX XXXXXX XXXX",
				"XXXXXXXXXXXXXXX",
				"XXXX XXXXXX XXXXX",
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX",
				"XXXXXXXXXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXXX",
				"XXXXXX XX XXXX XXXX XXX"
			)
		),
		array(
			"cards" => array("solo", "switch"),
			"length" => "16,18,19",
			"formats" => array(
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXXX",
				"XXXXXX XX XXXX XXXX XXX"
			)
		),
		array(
			"cards" => array("laser"),
			"length" => "16-19",
			"formats" => array(
				"XXXXXXXXXXXXXXXX",
				"XXXX XXXX XXXX XXXX",
				"XXXXXX XXXXXX XXXX",
				"XXX XXXXX XXXXX XXX",
				"XXXXXX XXXXXXXXXX",
				"XXXXXXXXXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXX",
				"XXXXXXXXXXXXXXXXXXX",
				"XXXXXX XX XXXX XXXX XXX"
			)
		)
	);

	public function __construct($runtimeContext) {
		for ($dpl=622126; $dpl<=622925; $dpl++) {
			$this->discoverPrefixList[] = $dpl;
		}

		for ($jpl=3528; $jpl<=3589; $jpl++) {
			$this->jcb16PrefixList[] = $jpl;
		}
		parent::__construct($runtimeContext);
	}


	public function generate($generator, $generationContextData) {
		$options = $generationContextData["generationOptions"];

		if ($options["cc_brand"] == "rand_card") {
			$options = $this->setRandomCardInfo($options);
		}


		$ccLength    = self::getRandomPANLength($options["cc_length"]);
		$ccFormat    = self::getRandomPANFormat($options["cc_format"], $options["cc_length"]);
		$ccSeparator = self::getRandomPANSeparator($options["cc_separator"], $options["cc_format"]);


		$prefixList = array();
		switch ($options["cc_brand"]) {
			case "mastercard":
				$prefixList = array("51", "52", "53", "54", "55");
				break;
			case "visa":
				$prefixList = array("4539", "4556", "4916", "4532", "4929", "40240071", "4485", "4716", "4");
				break;
			case "visa_electron":
				$prefixList = array("4026", "417500", "4508", "4844", "4913", "4917");
				break;
			case "amex":
				$prefixList = array("34", "37");
				break;
			case "discover":
				$prefixList = array("6011", "644", "645", "646", "647", "648", "649", "65");
				break;
			case "carte_blanche":
				$prefixList = array("300", "301", "302", "303", "304", "305");
				break;
			case "diners_club_international":
				$prefixList = array("36");
				break;
			case "enroute":
				$prefixList = array("2014", "2149");
				break;
			case "jcb":
				if ($ccLength == 15) {
					$prefixList = array("31", "309");
				} else {
					$prefixList = array("2131", "1800");
				}
				break;
			case "maestro":
				$prefixList = array("5018", "5020", "5038", "6304", "6759", "6761", "6762", "6763", "5893", "58", "56", "57");
				break;
			case "solo":
				$prefixList = array("6334", "6767");
				break;
			case "switch":
				$prefixList = array("4903", "4905", "4911", "4936", "564182", "633110", "6333", "6759");
				break;
			case "laser":
				$prefixList = array("6304", "6706", "6771", "6709");
				break;
		}

		$card = self::getCreditCardNumber($prefixList, $ccLength);
		$cardNumber = $this->convertFormat($ccLength, $ccFormat, $ccSeparator, $card);

		return array(
			"display" => $cardNumber
		);
	}

	public function setRandomCardInfo($options) {
		$selectedCard = $options["cc_random_card"][array_rand($options["cc_random_card"])];

		$cardData = array();
		foreach ($this->data as $cardGroup) {
			if (in_array($selectedCard, $cardGroup["cards"])) {
				$cardData["length"] = $cardGroup["length"];
				$cardData["formats"] = $cardGroup["formats"];
				break;
			}
		}

		if (empty($cardData)) {
			return false;
		}

		$options["cc_format"] = $cardData["formats"][array_rand($cardData["formats"])];
		$options["cc_length"] = self::getRandomPANLength($cardData["length"]);

		return $options;
	}

	public function getRowGenerationOptions($generator, $postdata, $colNum, $numCols) {
		return array(
			"cc_brand"	     => $postdata["dtExample_$colNum"],
			"cc_separator"   => $postdata["dtOptionPAN_sep_$colNum"],
			"cc_format"      => $postdata["dtOption_$colNum"],
			"cc_length"      => $postdata["dtOptionPAN_digit_$colNum"],
			"cc_random_card" => $postdata["dtOptionPAN_randomCardFormat_$colNum"]
		);
	}

	public function getExampleColumnHTML() {
		$L = Core::$language->getCurrentLanguageStrings();

		$html =<<< END
	<select name="dtExample_%ROW%" id="dtExample_%ROW%">
		<option value="">{$L["please_select"]}</option>
		<option value="mastercard">{$this->L["mastercard"]}</option>
		<option value="visa">{$this->L["visa"]}</option>
		<option value="visa_electron">{$this->L["visa_electron"]}</option>
		<option value="amex">{$this->L["americanexpress"]}</option>
		<option value="discover">{$this->L["discover"]}</option>
		<option value="carte_blanche">{$this->L["carte_blanche"]}</option>
		<option value="diners_club_international">{$this->L["diners_club_international"]}</option>
		<option value="enroute">{$this->L["enroute"]}</option>
		<option value="jcb">{$this->L["jcb"]}</option>
		<option value="maestro">{$this->L["maestro"]}</option>
		<option value="solo">{$this->L["solo"]}</option>
		<option value="switch">{$this->L["switch"]}</option>
		<option value="laser">{$this->L["laser"]}</option>
		<option value="rand_card">{$this->L["rand_card"]}</option>
	</select>
END;
		return $html;
	}

	public function getOptionsColumnHTML() {
		$html =<<< END
<span id="dtOptionPAN_cardDigitSection_%ROW%">
	{$this->L["length"]}
	<input type="text" name="dtOptionPAN_digit_%ROW%" id="dtOptionPAN_digit_%ROW%" style="width: 60px" readonly="readonly" />
</span>

<span id="dtOptionPAN_cardSeparator_%ROW%">
	{$this->L["separators"]}
	<input type="text" name="dtOptionPAN_sep_%ROW%" id="dtOptionPAN_sep_%ROW%" style="width: 78px" value=" |:|*|.|-" title="{$this->L["separator_help"]}" />
</span>

<span id="dtOptionPAN_cardFormat_%ROW%">
	{$this->L["ccformats"]}
	<textarea name="dtOption_%ROW%" id="dtOption_%ROW%" title="{$this->L["format_title"]}" style="height: 100px; width: 260px"></textarea>
</span>

<span id="dtOptionPAN_randomCardFormatSection_%ROW%" style="display:none;">
	{$this->L["ccrandom"]}
	<select multiple="multiple" name="dtOptionPAN_randomCardFormat_%ROW%[]" id="dtOptionPAN_randomCardFormat_%ROW%" title="{$this->L["rand_brand_title"]}" style="height: 100px; width: 260px">
		<option value="mastercard">{$this->L["mastercard"]}</option>
		<option value="visa">{$this->L["visa"]}</option>
		<option value="visa_electron">{$this->L["visa_electron"]}</option>
		<option value="amex">{$this->L["americanexpress"]}</option>
		<option value="discover">{$this->L["discover"]}</option>
		<option value="carte_blanche">{$this->L["carte_blanche"]}</option>
		<option value="diners_club_international">{$this->L["diners_club_international"]}</option>
		<option value="enroute">{$this->L["enroute"]}</option>
		<option value="jcb">{$this->L["jcb"]}</option>
		<option value="maestro">{$this->L["maestro"]}</option>
		<option value="solo">{$this->L["solo"]}</option>
		<option value="switch">{$this->L["switch"]}</option>
		<option value="laser">{$this->L["laser"]}</option>
	</select>
</span>
END;
		return $html;
	}

	public function getDataTypeMetadata() {
		return array(
			"SQLField" => "varchar(255)",
			"SQLField_Oracle" => "varchar2(255)",
			"SQLField_MSSQL" => "VARCHAR(255) NULL"
		);
	}

	public function getHelpHTML() {
		$html =<<<EOF
<p>
	{$this->L["pan_help_intro"]}
	<b>{$this->L["mastercard"]}</b>, <b>{$this->L["visa"]}</b>, <b>{$this->L["visa_electron"]}</b>,
	<b>{$this->L["americanexpress"]}</b>, <b>{$this->L["discover"]}</b>, <b>{$this->L["american_diners"]}</b>,
	<b>{$this->L["carte_blanche"]}</b>, <b>{$this->L["diners_club_international"]}</b>, <b>{$this->L["enroute"]}</b>,
	<b>{$this->L["jcb"]}</b>, <b>{$this->L["maestro"]}</b>, <b>{$this->L["solo"]}</b>,
	<b>{$this->L["switch"]}</b>, <b>{$this->L["laser"]}</b>.
</p>
EOF;

		return $html;
	}


	private static function getCreditCardNumber($prefixList, $length) {
		$ccNumber = $prefixList[array_rand($prefixList)];

		// generate digits
		while (strlen($ccNumber)<($length-1)) {
			$ccNumber .= mt_rand(0,9);
		}

		// calculate sum
		$sum = 0;
		$pos = 0;

		$reversedCCnumber = strrev($ccNumber);
		while ($pos < $length - 1) {
			$odd = $reversedCCnumber[$pos]*2;
			if ($odd > 9) {
				$odd -= 9;
			}
			$sum += $odd;

			if ($pos != ($length - 2)) {
				$sum += $reversedCCnumber[ $pos +1 ];
			}
			$pos += 2;
		}

		// calculate check digit
		$checkDigit = ((floor($sum/10) + 1) * 10 - $sum) % 10;
		$ccNumber .= $checkDigit;

		return $ccNumber;
	}


	/**
	 * @param $ccLength
	 * @param $ccFormat
	 * @param $ccSeparator
	 * @param $ccNumber
	 * @return array|bool|string
	 */
	private static function convertFormat($ccLength, $ccFormat, $ccSeparator, $ccNumber) {

		// TODO pity we need this extra test on each call
		if ($ccLength == strlen($ccNumber)) {
			$a = self::convertXtoNumber($ccFormat, $ccNumber);

			if ($a == $ccNumber) {
				return $a;
			} else {
				return implode($ccSeparator, $a);
			}
		} else {
			return false;
		}
	}

	/**
	 * Convert X's to the specified number
	 */
	private static function convertXtoNumber($chosen_format, $ccnumber){
		$positions = array();
		$pos = -1;
		while (($pos = strpos($chosen_format, " ", $pos+1)) !== false) {
			$positions[] = $pos;
		}

		if (empty($positions)) {
			return $ccnumber;
		}

		$result   = array();
		$result_f = array();
		$j = 1;

		for ($i=0; $i<count($positions); $i++) {
			$result[$i] = substr($ccnumber, 0, $positions[$i]-$i);
		}

		$result_f[0] = ($result[0]);
		for ($i=0; $i<count($positions)-1; $i++) {
			$result_f[$j] = substr($result[$j], $positions[$i]-$i);
			$j++;
		}
		$result_f[count($positions)] = substr($ccnumber, ($positions[count($positions)-1])-(count($positions)-1));

		return $result_f;
	}


	private static function getRandomPANFormat($userSelectedFormats, $randCardLength) {

		// if no format is selected then by default continuous number of that length will be displayed
		if ($userSelectedFormats == "") {
			return str_repeat("X", $randCardLength);
		}

		$formats = explode("\n", $userSelectedFormats);

		$sortedFormat = array();
		$not_i = 0;

		for ($fc = 0; $fc < count($formats); $fc++){
			$count_X = "0"; // get count of X's to match with the card length

			for ($i=0; $i<strlen($formats[$fc]); $i++) {
				if (substr($formats[$fc], $i, 1) == "X") {
					$count_X++;
				}
			}

			if ($count_X == $randCardLength) {
				$sortedFormat[$not_i] = $formats[$fc];
				$not_i++;
			}
		}

		$chosenFormat = "";
		if (count($sortedFormat) >= 1) {
			$chosenFormat = $sortedFormat[mt_rand(0, count($sortedFormat)-1)];
		}

		return trim($chosenFormat);
	}


	// will give a random separator
	private static function getRandomPANSeparator($separators, $randCardFormat) {

		$chosenSep = "";
		if (preg_match("/[^X]/", $randCardFormat)) {

			$separatorList = explode("|", $separators);
			$chosenSep = $separatorList[rand(0, count($separatorList)-1)];

			// if no separator was entered
			if ($separators == "") {
				$chosenSep = " ";
			}
		}

		return $chosenSep;
	}


	private static function getRandomPANLength($userSelectedLength) {

		// if there's more than 1 card length then pick a random one
		if ($userSelectedLength == "12-19") {
			$userSelectedLength = "12,13,14,15,16,17,18,19";
		} else if ($userSelectedLength == "16-19") {
			$userSelectedLength = "16,17,18,19";
		}

		$lengths = explode(",", $userSelectedLength);
		$chosenLength = 0;
		if (count($lengths) >= 1) {
			$chosenLength = $lengths[mt_rand(0, count($lengths)-1)];
		}

		return $chosenLength;
	}
}