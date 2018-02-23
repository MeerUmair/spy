<?php

/*
  echo "start detecting...<br />";
  $half = "ｷﾞ";
  $full = "全形";

  if (CharWidthDetector::isFullWidth($full)) {
  echo 'full-width'; // ZEN_KAKU
  } else {
  echo 'half-width'; // HAN_KAKU
  }
 */

class Rack_Jp_Validator_Helper_CharCheck extends Mage_Core_Helper_Abstract {

    /**
     * Detects if the supplied string is having a han-kaku character (half-width)
     * @param $str
     * @return unknown_type
     */
    public static function isFullWidth($str) {
        $unicode = self::_utf8ToUnicode($str);
        $ret = true;

        foreach ($unicode as $uni) {
            $chk = self::_isJapanese($uni);
            if ($chk == 0 || $chk == 2) {
                //non-japanese or han kaku found!
                $ret = false;
                break;
            }
        }

        return $ret;
    }

    /**
     * Detects if the supplied string can be used by Kuroneko Yamato.
     *
     * ascii [\x20-\x7e] 
     * 記号 全角スペース〜〓 （？ \xa1\xa9はダメ）　 [\xa1\xa1-\xa1\xa8]  [\xa1\xaa-\xa2\xae] 
     * 全角数字 ０-９ [\xa3\xb0-\xa3\xb9]
     * 全角英字 ａ-ｚＡ-Ｚ [\xa3\xc1-\xa3\xda\xa3\xe1-\xa3\xfa]
     * ひらがな ぁ-ん [\xa4\xa1-\xa4\xf3]
     * カタカナ ァ-ヶ [\xa5\xa1-\xa5\xf6]
     * 第一水準漢字 亜-腕[\xb0\xa1-\xcf\xd3] 第二水準漢字 弌-熙[\xd0\xa1-\xf4\xa6](JIS X 0208 statndard level 1/2 Kanji set) 
     * 
     * @param $str
     * @return unknown_type
     */
    public static function isAvailableChar($str) {
        if (strlen($str) == 0) {
            return true;
        }
        if (mb_ereg('^[\x20-\x7e]+$', $str)) {
            return true;
        }
        $euc_txt = mb_convert_encoding($str, "EUC-JP", "utf-8");
        if (strstr($euc_txt, '?')) {
            return false;
        }
        $char_code = mb_internal_encoding();
        mb_internal_encoding("EUC-JP");
        mb_regex_encoding("EUC-JP");
        if (mb_ereg('^[\x20-\x7e\xa1\xa1-\xa1\xa8\xa1\xaa-\xa2\xae\xa3\xb0-\xa3\xb9\xa3\xc1-\xa3\xda\xa3\xe1-\xa3\xfa\xa4\xa1-\xa4\xf3\xa5\xa1-\xa5\xf6\xb0\xa1-\xcf\xd3\xd0\xa1-\xf4\xa6]+$', $euc_txt)) {
            $ret = true;
        } else {
            $ret = false;
        }
        mb_internal_encoding($char_code);
        mb_regex_encoding($char_code);
        return $ret;
    }

    protected static function _utf8ToUnicode($str) {
        $unicode = array();
        $values = array();
        $lookingFor = 1;

        for ($i = 0; $i < strlen($str); $i++) {
            $thisValue = ord($str[$i]);
            if ($thisValue < 128) {
                $unicode[] = $thisValue;
            } else {
                if (count($values) == 0)
                    $lookingFor = ( $thisValue < 224 ) ? 2 : 3;

                $values[] = $thisValue;

                if (count($values) == $lookingFor) {
                    $number = ( $lookingFor == 3 ) ?
                            ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ) :
                            ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );

                    $unicode[] = $number;
                    $values = array();
                    $lookingFor = 1;
                }
            }
        }
        return $unicode;
    }

    /**
     * Returns if a given unicode value is a japanese character
     * Returns 	0 if not japanese
     * 			1 if Zen Kaku
     * 			2 if Han Kaku
     * 			3 if Not Han Kaku but Japanese Character (Hiragana, Kanji, etc)
     *
     * @param $unicodeVal
     * @return int japanese
     */
    protected static function _isJapanese($unicodeVal) {
        $ret = 0;
        //unicodeVal is a single value only
        if ($unicodeVal == 8221) {
            //right double quotation
            $ret = 3;
        } elseif ($unicodeVal >= 12288 && $unicodeVal <= 12351) {
            //Japanese Style Punctuation
            $ret = 3;
        } elseif ($unicodeVal >= 12352 && $unicodeVal <= 12447) {
            //Hiragana
            $ret = 3;
        } elseif ($unicodeVal >= 12448 && $unicodeVal <= 12543) {
            //Katakana
            $ret = 3;
        } elseif ($unicodeVal >= 12784 && $unicodeVal <= 12799) {
            $ret = 3;
        } elseif ($unicodeVal >= 12800 && $unicodeVal <= 13054) {
            $ret = 3;
        } elseif ($unicodeVal >= 65280 && $unicodeVal <= 65376) {
            //full width roman character (Zen Kaku)
            $ret = 1;
        } elseif ($unicodeVal >= 65377 && $unicodeVal <= 65439) {
            //half width character (Han Kaku)
            $ret = 2;
        } elseif ($unicodeVal >= 65504 && $unicodeVal <= 65510) {
            //full width character (Zen Kaku)
            $ret = 1;
        } elseif ($unicodeVal >= 65512 && $unicodeVal <= 65518) {
            //half width character (Han Kaku)
            $ret = 2;
        } elseif ($unicodeVal >= 19968 && $unicodeVal <= 40879) {
            //common and uncommon kanji
            $ret = 3;
        } elseif ($unicodeVal >= 13312 && $unicodeVal <= 19903) {
            //Rare Kanji
            $ret = 3;
        }
        return $ret;
    }

}