<?php
define('NORMAL', 0);
define('WARNING', 1);
define('ERROR', 2);

class DataAnalysis {
    public $ksort = array();
    public $week = array();
    public $day = array();
    public $first_time = 0;
    public $last_time = 0;
    public $last_value = 0;
    // 波动比率与报警级别
    public $ratio = array(
            ERROR => array('min'=>0.5, 'max'=>3),
            WARNING => array('min'=>0.8, 'max'=>2),
            NORMAL => array('min'=>1, 'max'=>1),
            );
    // 允许数据缺失最长时间
    public $accept_time = array(
            ERROR => 0,
            WARNING => 0,
            );
    public function __construct($data = array()) {
        if(!empty($data)) {
            $this->push_data($data);
        }
    }
    public function push_data($data, $last_time = 0, $accept_time = array()) {
        if(!empty($accept_time)) {
            foreach($this->accept_time as $level => $value) {
                if(isset($accept_time[$level])) {
                    $this->accept_time[$level] = $accept_time[$level];
                }
            }
        }
        $this->ksort = $data;
        $this->week = $this->day = array();
        ksort($this->ksort, SORT_NUMERIC);
        $last = array_keys($this->ksort);
        // 如果传入时间则以传入时间为监控时间点，否则以最新数据时间点监控
        $this->first_time = $last[0];
        $this->last_time = $last_time > 0 ? $last_time : $last[(count($last) - 1)];
        $this->last_value = isset($this->ksort[$this->last_time]) ? $this->ksort[$this->last_time] : false;
        if($this->last_value === false) {
            if($this->data_full_rate($this->ksort) < 0.6) {
                throw new Exception("无当前".date('Y-m-d H:i:s', $this->last_time)."数据，历史覆盖不足60%，最近数据: " . date('Y-m-d H:i:s',$last[(count($last) - 1)]), NORMAL);
            }
            foreach($this->accept_time as $level => $value) {
                if($this->last_time - $last[(count($last) - 1)] >= $value) {
                    throw new Exception("无当前".date('Y-m-d H:i:s', $this->last_time)."数据，最近数据: " . date('Y-m-d H:i:s',$last[(count($last) - 1)]), $level);
                }
            }
            throw new Exception("无当前".date('Y-m-d H:i:s', $this->last_time)."数据，历史覆盖超过60%，最近数据: " . date('Y-m-d H:i:s',$last[(count($last) - 1)]), NORMAL);
        }
        if($this->last_value <= 0) {
            throw new Exception("数据异常，最近数据: " . date('Y-m-d H:i:s',$this->last_time), ERROR);
        }
        krsort($this->ksort, SORT_NUMERIC);
        foreach($this->ksort as $time => $value) {
            if(($this->last_time - $time) % (86400) == 0) {
                $this->day[$time] = $value;
            }
            if(($this->last_time - $time) % (7 * 86400) == 0) {
                $this->week[$time] = $value;
            }
        }
        arsort($this->day, SORT_NUMERIC);
        arsort($this->week, SORT_NUMERIC);
    }

    public function within_min_max($args = array()) {
        if(!empty($args)) {
            foreach($this->ratio as $level => $option) {
                foreach($option as $type => $value) {
                    if(isset($args[$level][$type])) {
                        $this->ratio[$level][$type] = $args[$level][$type];
                    }
                }
            }
        }
        // 日范围
        list($dmin, $dmax) = $this->avg_range($this->day);
        foreach($this->ratio as $level => $option) {
            if($this->last_value > $dmax * $option['max'] || $this->last_value < $dmin * $option['min']) {
                throw new Exception("超出日同比范围: {$dmin} < {$this->last_value} < {$dmax}, 发生时间: " . date('Y-m-d H:i:s', $this->last_time), $level);
            }
        }

        // 周范围
        list($wmin, $wmax) = $this->avg_range($this->week);
        foreach($this->ratio as $level => $option) {
            if($this->last_value > $wmax * $option['max'] || $this->last_value < $wmin * $option['min']) {
                throw new Exception("超出周同比范围: {$wmin} < {$this->last_value} < {$wmax}, 发生时间: " . date('Y-m-d H:i:s', $this->last_time), $level);
            }
        }
        return true;
    }

    public function data_full_rate($data) {
        $have = $count = 0;
        for($i = $this->last_time; $i > $this->first_time; $i-=86400) {
            if(isset($data[$i])) {
                $have++;
            }
            $count++;
        }
        return round($have / $count, 2);
    }

    public function avg_range($data) {
        $count = count($data);
        if($count < 2) {
            throw new Exception("数据不足以计算监控, 发生时间: " . date('Y-m-d H:i:s', $this->last_time), NORMAL);
        }
        $i = $min = $max = 0;
        $n = ceil($count * 0.1); $n = $n < 3 ? 3 : $n;
        foreach($data as $t => $v) {
            if($count - $i <= $n) {
                $min += $v;
            }
            if($i < $n) {
                $max += $v;
            }
            $i++;
        }
        $min = round($min / $n, 2);
        $max = round($max / $n, 2);
        return array($min, $max);
    }

    public function within_trend($minratio = 1, $maxratio = 1.5) {
        return true;
    }

}
