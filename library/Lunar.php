<?php
declare(strict_types=1);

namespace esp\helper\library;


/**
 * 阴历阳历互换
 *
 * Class Lunar
 * @package library
 */
class Lunar
{
    private $MIN_YEAR = 1891;
    private $MAX_YEAR = 2100;
    private $lunarInfo = array(
        array(0, 2, 9, 21936), array(6, 1, 30, 9656), array(0, 2, 17, 9584), array(0, 2, 6, 21168), array(5, 1, 26, 43344), array(0, 2, 13, 59728),
        array(0, 2, 2, 27296), array(3, 1, 22, 44368), array(0, 2, 10, 43856), array(8, 1, 30, 19304), array(0, 2, 19, 19168), array(0, 2, 8, 42352),
        array(5, 1, 29, 21096), array(0, 2, 16, 53856), array(0, 2, 4, 55632), array(4, 1, 25, 27304), array(0, 2, 13, 22176), array(0, 2, 2, 39632),
        array(2, 1, 22, 19176), array(0, 2, 10, 19168), array(6, 1, 30, 42200), array(0, 2, 18, 42192), array(0, 2, 6, 53840), array(5, 1, 26, 54568),
        array(0, 2, 14, 46400), array(0, 2, 3, 54944), array(2, 1, 23, 38608), array(0, 2, 11, 38320), array(7, 2, 1, 18872), array(0, 2, 20, 18800),
        array(0, 2, 8, 42160), array(5, 1, 28, 45656), array(0, 2, 16, 27216), array(0, 2, 5, 27968), array(4, 1, 24, 44456), array(0, 2, 13, 11104),
        array(0, 2, 2, 38256), array(2, 1, 23, 18808), array(0, 2, 10, 18800), array(6, 1, 30, 25776), array(0, 2, 17, 54432), array(0, 2, 6, 59984),
        array(5, 1, 26, 27976), array(0, 2, 14, 23248), array(0, 2, 4, 11104), array(3, 1, 24, 37744), array(0, 2, 11, 37600), array(7, 1, 31, 51560),
        array(0, 2, 19, 51536), array(0, 2, 8, 54432), array(6, 1, 27, 55888), array(0, 2, 15, 46416), array(0, 2, 5, 22176), array(4, 1, 25, 43736),
        array(0, 2, 13, 9680), array(0, 2, 2, 37584), array(2, 1, 22, 51544), array(0, 2, 10, 43344), array(7, 1, 29, 46248), array(0, 2, 17, 27808),
        array(0, 2, 6, 46416), array(5, 1, 27, 21928), array(0, 2, 14, 19872), array(0, 2, 3, 42416), array(3, 1, 24, 21176), array(0, 2, 12, 21168),
        array(8, 1, 31, 43344), array(0, 2, 18, 59728), array(0, 2, 8, 27296), array(6, 1, 28, 44368), array(0, 2, 15, 43856), array(0, 2, 5, 19296),
        array(4, 1, 25, 42352), array(0, 2, 13, 42352), array(0, 2, 2, 21088), array(3, 1, 21, 59696), array(0, 2, 9, 55632), array(7, 1, 30, 23208),
        array(0, 2, 17, 22176), array(0, 2, 6, 38608), array(5, 1, 27, 19176), array(0, 2, 15, 19152), array(0, 2, 3, 42192), array(4, 1, 23, 53864),
        array(0, 2, 11, 53840), array(8, 1, 31, 54568), array(0, 2, 18, 46400), array(0, 2, 7, 46752), array(6, 1, 28, 38608), array(0, 2, 16, 38320),
        array(0, 2, 5, 18864), array(4, 1, 25, 42168), array(0, 2, 13, 42160), array(10, 2, 2, 45656), array(0, 2, 20, 27216), array(0, 2, 9, 27968),
        array(6, 1, 29, 44448), array(0, 2, 17, 43872), array(0, 2, 6, 38256), array(5, 1, 27, 18808), array(0, 2, 15, 18800), array(0, 2, 4, 25776),
        array(3, 1, 23, 27216), array(0, 2, 10, 59984), array(8, 1, 31, 27432), array(0, 2, 19, 23232), array(0, 2, 7, 43872), array(5, 1, 28, 37736),
        array(0, 2, 16, 37600), array(0, 2, 5, 51552), array(4, 1, 24, 54440), array(0, 2, 12, 54432), array(0, 2, 1, 55888), array(2, 1, 22, 23208),
        array(0, 2, 9, 22176), array(7, 1, 29, 43736), array(0, 2, 18, 9680), array(0, 2, 7, 37584), array(5, 1, 26, 51544), array(0, 2, 14, 43344),
        array(0, 2, 3, 46240), array(4, 1, 23, 46416), array(0, 2, 10, 44368), array(9, 1, 31, 21928), array(0, 2, 19, 19360), array(0, 2, 8, 42416),
        array(6, 1, 28, 21176), array(0, 2, 16, 21168), array(0, 2, 5, 43312), array(4, 1, 25, 29864), array(0, 2, 12, 27296), array(0, 2, 1, 44368),
        array(2, 1, 22, 19880), array(0, 2, 10, 19296), array(6, 1, 29, 42352), array(0, 2, 17, 42208), array(0, 2, 6, 53856), array(5, 1, 26, 59696),
        array(0, 2, 13, 54576), array(0, 2, 3, 23200), array(3, 1, 23, 27472), array(0, 2, 11, 38608), array(11, 1, 31, 19176), array(0, 2, 19, 19152),
        array(0, 2, 8, 42192), array(6, 1, 28, 53848), array(0, 2, 15, 53840), array(0, 2, 4, 54560), array(5, 1, 24, 55968), array(0, 2, 12, 46496),
        array(0, 2, 1, 22224), array(2, 1, 22, 19160), array(0, 2, 10, 18864), array(7, 1, 30, 42168), array(0, 2, 17, 42160), array(0, 2, 6, 43600),
        array(5, 1, 26, 46376), array(0, 2, 14, 27936), array(0, 2, 2, 44448), array(3, 1, 23, 21936), array(0, 2, 11, 37744), array(8, 2, 1, 18808),
        array(0, 2, 19, 18800), array(0, 2, 8, 25776), array(6, 1, 28, 27216), array(0, 2, 15, 59984), array(0, 2, 4, 27424), array(4, 1, 24, 43872),
        array(0, 2, 12, 43744), array(0, 2, 2, 37600), array(3, 1, 21, 51568), array(0, 2, 9, 51552), array(7, 1, 29, 54440), array(0, 2, 17, 54432),
        array(0, 2, 5, 55888), array(5, 1, 26, 23208), array(0, 2, 14, 22176), array(0, 2, 3, 42704), array(4, 1, 23, 21224), array(0, 2, 11, 21200),
        array(8, 1, 31, 43352), array(0, 2, 19, 43344), array(0, 2, 7, 46240), array(6, 1, 27, 46416), array(0, 2, 15, 44368), array(0, 2, 5, 21920),
        array(4, 1, 24, 42448), array(0, 2, 12, 42416), array(0, 2, 2, 21168), array(3, 1, 22, 43320), array(0, 2, 9, 26928), array(7, 1, 29, 29336),
        array(0, 2, 17, 27296), array(0, 2, 6, 44368), array(5, 1, 26, 19880), array(0, 2, 14, 19296), array(0, 2, 3, 42352), array(4, 1, 24, 21104),
        array(0, 2, 10, 53856), array(8, 1, 30, 59696), array(0, 2, 18, 54560), array(0, 2, 7, 55968), array(6, 1, 27, 27472), array(0, 2, 15, 22224),
        array(0, 2, 5, 19168), array(4, 1, 25, 42216), array(0, 2, 12, 42192), array(0, 2, 1, 53584), array(2, 1, 21, 55592), array(0, 2, 9, 54560)
    );
    private $value;

    public function __construct()
    {
        $this->value = new class {
            private $_value = [];

            public function set(string $key, $value)
            {
                $this->_value[$key] = $value;
                return $this;
            }

            public function value()
            {
                /**
                 * Array
                 * (
                 * [cn_year] => 二〇一一
                 * [cn_month] => 腊月
                 * [cn_day] => 初七
                 * [year_name] => 辛卯年
                 * [zodiac] => 兔
                 * [leap] => 0
                 * [t_month] => 12
                 * [t_day] => 7
                 * )
                 */
                $this->_value['label'] = "{$this->_value['cn_year']}({$this->_value['year_name']}) {$this->_value['cn_month']}{$this->_value['cn_day']}";
                return $this->_value;
            }
        };
    }

    /**
     * 将阳历转换为阴历
     * @param int  year 公历-年
     * @param int  month 公历-月
     * @param int  date 公历-日
     * @return array
     */
    function convertSolarToLunar(int $year, int $month, int $date)
    {
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        if ($year == $this->MIN_YEAR && $month <= 2 && $date <= 9) {
            return array(1891, '正月', '初一', '辛卯', 1, 1, '兔');
        }
        $diff = $this->getDaysBetweenSolar($year, $month, $date, $yearData[1], $yearData[2]);

        return $this->getLunarByBetween($year, $diff);
    }

    /**
     * 将阴历转换为阳历
     * @param int year 阴历-年
     * @param int month 阴历-月，闰月处理：例如如果当年闰五月，那么第二个五月就传六月，相当于阴历有13个月，只是有的时候第13个月的天数为0
     * @param int date 阴历-日
     * @return array
     */
    function convertLunarToSolar(int $year, int $month, int $date)
    {
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        $between = $this->getDaysBetweenLunar($year, $month, $date);
        $res = mktime(0, 0, 0, $yearData[1], $yearData[2], $year);
        $res = date('Y-m-d', $res + $between * 24 * 60 * 60);
        $day = explode('-', $res);
        $year = $day[0];
        $month = $day[1];
        $day = $day[2];
        return array($year, $month, $day);
    }

    /**
     * 判断是否是闰年
     * @param int $year
     * @return bool
     */
    private function isLeapYear(int $year)
    {
        return (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0));
    }

    /**
     * 获取干支纪年
     * @param int $year
     * @return string
     */
    private function getLunarYearName(int $year)
    {
        $sky = array('庚', '辛', '壬', '癸', '甲', '乙', '丙', '丁', '戊', '己');
        $earth = array('申', '酉', '戌', '亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未');
        $year = $year . '';
        return $sky[$year{3}] . $earth[$year % 12] . '年';
    }

    /**
     * 根据阴历年获取生肖
     * @param int $year 阴历年
     * @return string
     */
    private function getYearZodiac(int $year)
    {
        $zodiac = array('猴', '鸡', '狗', '猪', '鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊');
        return $zodiac[$year % 12];
    }

    /**
     * 获取阳历月份的天数
     * @param int year 阳历-年
     * @param int month 阳历-月
     * @return int
     */
    private function getSolarMonthDays(int $year, int $month)
    {
        $monthHash = array('1' => 31, '2' => $this->isLeapYear($year) ? 29 : 28, '3' => 31, '4' => 30, '5' => 31, '6' => 30, '7' => 31, '8' => 31, '9' => 30, '10' => 31, '11' => 30, '12' => 31);
        return $monthHash["$month"];
    }

    /**
     * 获取阴历月份的天数
     * @param int year 阴历-年
     * @param int month 阴历-月，从一月开始
     * @return array|mixed
     */
    private function getLunarMonthDays(int $year, int $month)
    {
        $monthData = $this->getLunarMonths($year);
        return $monthData[$month - 1];
    }

    /**
     * 获取阴历每月的天数的数组
     * @param int year
     * @return array
     */
    private function getLunarMonths(int $year)
    {
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        $leapMonth = $yearData[0];
        $bit = decbin($yearData[3]);
        $bitArray = [];
        for ($i = 0; $i < strlen($bit); $i++) {
            $bitArray[$i] = substr($bit, $i, 1);
        }
        for ($k = 0, $kLen = 16 - count($bitArray); $k < $kLen; $k++) {
            array_unshift($bitArray, '0');
        }
        $bitArray = array_slice($bitArray, 0, ($leapMonth == 0 ? 12 : 13));
        for ($i = 0; $i < count($bitArray); $i++) {
            $bitArray[$i] = $bitArray[$i] + 29;
        }
        return $bitArray;
    }

    /**
     * 获取农历每年的天数
     * @param int year 农历年份
     * @return mixed
     */
    private function getLunarYearDays(int $year)
    {
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        $monthArray = $this->getLunarYearMonths($year);
        $len = count($monthArray);
        return ($monthArray[$len - 1] == 0 ? $monthArray[$len - 2] : $monthArray[$len - 1]);
    }

    /**
     * @param int $year
     * @return array
     */
    private function getLunarYearMonths(int $year)
    {
        $monthData = $this->getLunarMonths($year);
        $res = array();
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        $len = ($yearData[0] == 0 ? 12 : 13);
        for ($i = 0; $i < $len; $i++) {
            $temp = 0;
            for ($j = 0; $j <= $i; $j++) {
                $temp += $monthData[$j];
            }
            array_push($res, $temp);
        }
        return $res;
    }

    /**
     * 获取闰月
     * @param int year 阴历年份
     * @return int
     */
    private function getLeapMonth(int $year)
    {
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        return $yearData[0];
    }

    /**
     * 计算阴历日期与正月初一相隔的天数
     * @param int year
     * @param int month
     * @param int date
     * @return int|mixed
     */
    private function getDaysBetweenLunar(int $year, int $month, int $date)
    {
        $yearMonth = $this->getLunarMonths($year);
        $res = 0;
        for ($i = 1; $i < $month; $i++) {
            $res += $yearMonth[$i - 1];
        }
        $res += $date - 1;
        return $res;
    }

    /**
     * 计算2个阳历日期之间的天数
     * @param int $year 阳历年
     * @param int $cMonth
     * @param int $cDate
     * @param int $dMonth 阴历正月对应的阳历月份
     * @param int $dDate 阴历初一对应的阳历天数
     * @return int
     */
    private function getDaysBetweenSolar(int $year, int $cMonth, int $cDate, int $dMonth, int $dDate)
    {
        $a = mktime(0, 0, 0, $cMonth, $cDate, $year);
        $b = mktime(0, 0, 0, $dMonth, $dDate, $year);
        return (int)ceil(($a - $b) / 24 / 3600);
    }

    /**
     * 根据距离正月初一的天数计算阴历日期
     * @param int year 阳历年
     * @param int between 天数
     * @return array
     */
    private function getLunarByBetween(int $year, int $between)
    {
//        $lunarArray = array();
        $tMonth = 0;
        $tDay = 0;
        $leapMonth = 0;
        if ($between == 0) {
//            array_push($lunarArray, $year, '正月', '初一');
            $this->value->set('cn_year', $year)->set('cn_month', '正月')->set('cn_day', '初一');
            $tMonth = 1;
            $tDay = 1;
        } else {
            $year = $between > 0 ? $year : ($year - 1);
            $yearMonth = $this->getLunarYearMonths($year);
            $leapMonth = $this->getLeapMonth($year);
            $between = $between > 0 ? $between : ($this->getLunarYearDays($year) + $between);
            for ($i = 0; $i < 13; $i++) {
                if ($between == $yearMonth[$i]) {
                    $tMonth = $i + 2;
                    $tDay = 1;
                    break;
                } else if ($between < $yearMonth[$i]) {
                    $tMonth = $i + 1;
                    $tDay = $between - (empty($yearMonth[$i - 1]) ? 0 : $yearMonth[$i - 1]) + 1;
                    break;
                }
            }
            $cMonth = ($leapMonth != 0 && $tMonth == $leapMonth + 1) ?
                ('闰' . $this->getCapitalNum($tMonth - 1, true)) :
                $this->getCapitalNum(($leapMonth != 0 && $leapMonth + 1 < $tMonth ? ($tMonth - 1) : $tMonth), true);

            $my_year = $this->toYear($year);
            $cn_day = $this->getCapitalNum($tDay, false);
//            array_push($lunarArray, $my_year, $cMonth, $cn_day);
            $this->value->set('cn_year', $my_year)->set('cn_month', $cMonth)->set('cn_day', $cn_day);
        }
        $yName = $this->getLunarYearName($year);
        $zodiac = $this->getYearZodiac($year);

        $this->value->set('year_name', $yName)->set('zodiac', $zodiac)->set('leap', $leapMonth);

        $this->value->set('t_month', $tMonth)->set('t_day', $tDay);

//        array_push($lunarArray, $yName);// 天干地支
//        array_push($lunarArray, $tMonth, $tDay);
//        array_push($lunarArray, $zodiac);// 12生肖
//        array_push($lunarArray, $leapMonth);// 闰几月
//        return $lunarArray;
        return $this->value->value();
    }


    /**
     * 转换成中文年份
     * @param int $year
     * @return string
     */
    private function toYear(int $year)
    {
        $arr = array("〇", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $year_arr = str_split($year);
        return $arr[$year_arr[0]] . $arr[$year_arr[1]] . $arr[$year_arr[2]] . $arr[$year_arr[3]];
    }


    /**
     * 中文年份转换成数字
     * @param string $str
     * @return string
     */
    private function toNumber(string $str)
    {
        $arr = array("〇" => '0', "一" => '1', "二" => '2', "三" => '3', "四" => '4', "五" => '5', "六" => '6', "七" => '7', "八" => '8', "九" => '9');
        $year_arr = preg_split('/(?<!^)(?!$)/u', $str);
        $str = $arr[$year_arr[0]] . $arr[$year_arr[1]] . $arr[$year_arr[2]] . $arr[$year_arr[3]];
        return $str;
    }

    /**
     * 获取数字的阴历叫法
     * @param int $num
     * @param bool $isMonth 是否是月份的数字
     * @return string
     */
    private function getCapitalNum(int $num, bool $isMonth)
    {
        $isMonth = $isMonth || false;
        $dateHash = array('0' => '', '1' => '一', '2' => '二', '3' => '三', '4' => '四', '5' => '五', '6' => '六', '7' => '七', '8' => '八', '9' => '九', '10' => '十 ');
        $monthHash = array('0' => '', '1' => '正月', '2' => '二月', '3' => '三月', '4' => '四月', '5' => '五月', '6' => '六月', '7' => '七月', '8' => '八月', '9' => '九月', '10' => '十月', '11' => '冬月', '12' => '腊月');
        $res = '';
        if ($isMonth) {
            $res = $monthHash[$num];
        } else {
            if ($num <= 10) {
                $res = '初' . $dateHash[$num];
            } else if ($num > 10 && $num < 20) {
                $res = '十' . $dateHash[$num - 10];
            } else if ($num == 20) {
                $res = "二十";
            } else if ($num > 20 && $num < 30) {
                $res = "廿" . $dateHash[$num - 20];
            } else if ($num == 30) {
                $res = "三十";
            }
        }
        return $res;
    }
}

