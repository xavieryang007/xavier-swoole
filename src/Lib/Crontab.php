<?php

namespace xavier\swoole\Lib;
/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 下午2:30
 */
class Crontab
{

    /**
     * 检查某时间($time)是否符合某个corntab时间计划($str_cron)
     *
     * @param int $time 时间戳
     * @param string $str_cron corntab的时间计划，如，"30 2 * * 1-5"
     *
     * @return bool/string 出错返回string（错误信息）
     */
    public static function check($time, $str_cron)
    {
        $format_time = self::format_timestamp($time);
        $format_cron = self::format_crontab($str_cron);
        if (!is_array($format_cron)) {
            return $format_cron;
        }
        return self::format_check($format_time, $format_cron);
    }

    /**
     * 使用格式化的数据检查某时间($format_time)是否符合某个corntab时间计划($format_cron)
     *
     * @param array $format_time self::format_timestamp()格式化时间戳得到
     * @param array $format_cron self::format_crontab()格式化的时间计划
     *
     * @return bool
     */
    public static function format_check(array $format_time, array $format_cron)
    {
        return (!$format_cron[0] || in_array($format_time[0], $format_cron[0]))
            && (!$format_cron[1] || in_array($format_time[1], $format_cron[1]))
            && (!$format_cron[2] || in_array($format_time[2], $format_cron[2]))
            && (!$format_cron[3] || in_array($format_time[3], $format_cron[3]))
            && (!$format_cron[4] || in_array($format_time[4], $format_cron[4]))
            && (!$format_cron[5] || in_array($format_time[5], $format_cron[5]));
    }

    /**
     * 格式化时间戳，以便比较
     *
     * @param int $time 时间戳
     *
     * @return array
     */
    public static function format_timestamp($time)
    {
        return explode('-', date('s-i-G-j-n-w', $time));
    }

    /**
     * 格式化crontab时间设置字符串,用于比较
     *
     * @param string $str_cron crontab的时间计划字符串，如"15 3 * * *"
     *
     * @return array/string 正确返回数组，出错返回字符串（错误信息）
     */
    public static function format_crontab($str_cron)
    {
        //格式检查
        $str_cron = trim($str_cron);
        $reg      = '#^((\*(/\d+)?|((\d+(-\d+)?)(?3)?)(,(?4))*))( (?2)){5}$#';
        if (!preg_match($reg, $str_cron)) {
            return '格式错误';
        }

        try {
            //分别解析分、时、日、月、周
            $arr_cron    = [];
            $parts       = explode(' ', $str_cron);
            $arr_cron[0] = self::parse_cron_part($parts[0], 0, 59);//秒
            $arr_cron[1] = self::parse_cron_part($parts[1], 0, 59);//分
            $arr_cron[2] = self::parse_cron_part($parts[2], 0, 59);//时
            $arr_cron[3] = self::parse_cron_part($parts[3], 1, 31);//日
            $arr_cron[4] = self::parse_cron_part($parts[4], 1, 12);//月
            $arr_cron[5] = self::parse_cron_part($parts[5], 0, 6);//周（0周日）
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $arr_cron;
    }

    /**
     * 解析crontab时间计划里一个部分(分、时、日、月、周)的取值列表
     * @param string $part 时间计划里的一个部分，被空格分隔后的一个部分
     * @param int $f_min 此部分的最小取值
     * @param int $f_max 此部分的最大取值
     *
     * @return array 若为空数组则表示可任意取值
     * @throws Exception
     */
    protected static function parse_cron_part($part, $f_min, $f_max)
    {
        $list = [];

        //处理"," -- 列表
        if (false !== strpos($part, ',')) {
            $arr = explode(',', $part);
            foreach ($arr as $v) {
                $tmp  = self::parse_cron_part($v, $f_min, $f_max);
                $list = array_merge($list, $tmp);
            }
            return $list;
        }

        //处理"/" -- 间隔
        $tmp  = explode('/', $part);
        $part = $tmp[0];
        $step = isset($tmp[1]) ? $tmp[1] : 1;

        //处理"-" -- 范围
        if (false !== strpos($part, '-')) {
            list($min, $max) = explode('-', $part);
            if ($min > $max) {
                throw new Exception('使用"-"设置范围时，左不能大于右');
            }
        } elseif ('*' == $part) {
            $min = $f_min;
            $max = $f_max;
        } else {//数字
            $min = $max = $part;
        }

        //空数组表示可以任意值
        if ($min == $f_min && $max == $f_max && $step == 1) {
            return $list;
        }

        //越界判断
        if ($min < $f_min || $max > $f_max) {
            throw new Exception('数值越界。应该：秒0-59 分0-59，时0-59，日1-31，月1-12，周0-6');
        }

        return $max - $min > $step ? range($min, $max, $step) : [(int)$min];
    }
}