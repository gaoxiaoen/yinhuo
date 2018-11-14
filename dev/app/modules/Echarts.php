<?php

/**
 * Class Echarts echart的简单实用封装类
 * 参考官方api http://echarts.baidu.com/option.html#title
 * author fancy 2017.05.04
 *
 * 数据结构
 *  array(
 *      "title" => '测试',
"legend" => array('data' => array("邮件营销", "联盟广告", "视频广告", "直接访问", "搜索引擎")),
"xaxis" => array("type" => "category", "boundaryGap" => "true", "data" => array("周一", "周二", "周三", "周四", "周五", "周六", "周日")),
"series" => array(
array("name" => "邮件营销", "type" => "line", "data" => array("120", "132", "101", "134", "90", "230", "210")),
array("name" => "联盟广告", "type" => "bar", "data" => array("220", "182", "191", "234", "290", "330", "310")),
array("name" => "视频广告", "type" => "pie", "data" => array("150", "232", "201", "154", "190", "330", "410")),
))
 *
 */

class Echarts
{
    private static $index = 0;

    public static function create($data,$width="1200px",$height="400px")
    {
        return self::render($data,$width,$height);
    }

    private static function render($data,$width,$height)
    {
        self::$index++;
        $index = self::$index;
        $html = "<div id=\"chart-$index\" style=\"width:$width;height:$height\"></div>
        ";

        $xaxis = "";
        $series = "";
        $legend = "";
        $color = "";

        if (empty($data)) {
            $data = array(
                'legend' => array(
                    'data' => array('-')
                ),
                'xaxis' => array(
                    'type' => 'category',
                    'boundaryGap' => 'false',
                    'data' => array('')
                ),
                'series' => array(
                    array(
                        'name' => '-',
                        'type' => 'line',
                        'itemStyle' => "{normal: {areaStyle: {type: 'default'}}}",
                        'data' => array()
                    ),
                )
            );
        }

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'legend':
                    foreach ($value as $k => $v) {
                        switch ($k) {
                            case 'data':
                                $legend = $k . ':' . json_encode($v);
                                break;
                        }
                    }
                    break;

                case 'xaxis':
                    foreach ($value as $k => $v) {
                        switch ($k) {
                            case 'axisLabel':
                                $xaxis[] = $k . ":'" . json_encode($v) . "'";
                                break;
                            case 'type':
                                $xaxis[] = $k . ":'" . $v . "'";
                                break;
                            case 'boundaryGap':
                                $xaxis[] = $k . ':' . $v;
                                break;
                            case 'data':
                                $xaxis[] = $k . ':' . json_encode($v);
                                break;
                        }
                    }
                    $xaxis = '{' . implode(', ', $xaxis) . '}';
                    break;

                case 'series':
                    foreach ($value as $list) {
                        $tmp = array();
                        foreach ($list as $k => $v) {
                            switch ($k) {
                                case 'name':
                                case 'type':
                                    $tmp[] = $k . ":'" . $v . "'";
                                    break;
                                case 'itemStyle':
                                    $tmp[] = $k . ':' . $v;
                                    break;
                                case 'data':
                                    $tmp[] = $k . ':' . json_encode($v);
                            }
                        }
                        $series[] = '{' . implode(', ', $tmp) . '}';
                    }
                    $series = implode(', ', $series);
                    break;
            }
        }
        
        $html .= <<<EOF
<script type="text/javascript">
       var myChart$index = echarts.init(document.getElementById('chart-$index'));
       var option$index = {
            title:{
                text:"${data['title']}"
            },
            tooltip:{trigger: 'axis'},
            legend:{
                $legend
            },
            toolbox: {
                show : true,
                    feature : {
                                mark : true,
                                dataView : {readOnly: false},
                                magicType:['line', 'bar'],
                                restore : true
                            }
                        },
            xAxis:[$xaxis],
            yAxis:[{type:'value'}],
            series:[$series]
        };
        myChart$index.setOption(option$index);

</script>
EOF;
        return $html;
    }

}