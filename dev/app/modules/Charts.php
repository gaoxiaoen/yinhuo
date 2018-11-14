<?php
/**
 * User: jecelyin 
 * Date: 12-1-31
 * Time: 下午4:01
 * flash图表输出类
 */
 
class Charts
{
    private static $index = 0;

    /**
     * @static
     * @param array $categories X坐标分组名称
     * @param array $dataset  具体数据，格式：array('name'=>array(1,2,3))
     * @param string|int $width
     * @param string|int $height
     * @param string $chartAttr
     * @return string
     */
    public static function renderZoomLine($categories, $dataset, $width=1000, $height=400, $chartAttr='')
    {
        //caption,subcaption
        $xml = "<chart $chartAttr compactDataMode='1' seriesNameInToolTip='1' formatNumberScale='0' showValues='0' dataSeparator='|' dynamicAxis='1' drawAnchors='0' bgColor='ffffff' showBorder='0' showVDivLines='1'>";

        foreach($dataset as $name => $vals)
        {
            $data = implode('|',$vals);
            $xml .= "<dataset seriesName='{$name}'>{$data}</dataset>";
        }

        $xml .= "<categories>".implode('|', $categories)."</categories>";

        $xml .= "</chart>";
        return self::render($xml, '/charts/ZoomLine.swf', $width, $height);
    }

    /**
     * @static
     * @param string $caption 图标名
     * @param array $dataset  具体数据，格式：array('name'=>array(1,2,3))
     * @param string|int $width
     * @param string|int $height
     * @param string $xAxisName x轴名
     * @param string $yAxisName y轴名
     * @return string
     */
    public static function renderColumn2D($caption, $xAxisName, $yAxisName, $dataset, $width=1000, $height=400)
    {
        $xml = "<chart palette='3' caption='{$caption}' xAxisName='{$xAxisName}' yAxisName='{$yAxisName}' showValues='1' decimals='0' formatNumberScale='0' rotateLabels='1' slantLabels='1'>";
                
        foreach($dataset as $name => $vals)
        {
            $xml .= "<set label='{$vals[0]}' value='{$vals[1]}' />";
        }

        $xml .= "</chart>";
        
        return self::render($xml, '/charts/Column2D.swf', $width, $height);
    }
    
    public static function renderMSLine($categories, $dataset, $width=1000, $height=400, $chartAttr='')
    {//<chart caption='Site hits per hour' subCaption='In Thousands' numdivlines='9' lineThickness='2' showValues='0' numVDivLines='22' formatNumberScale='1' labelDisplay='ROTATE' slantLabels='1' anchorRadius='2' anchorBgAlpha='50' showAlternateVGridColor='1' anchorAlpha='100' animation='1' limitsDecimalPrecision='0' divLineDecimalPrecision='1'>
        $xml = "<chart $chartAttr bgColor='ffffff' showBorder='0' showVDivLines='1' seriesNameInToolTip='1' formatNumberScale='0' showValues='0'  numVDivLines='".(count($categories)-2)."' divLineAlpha='30'>";

        foreach($dataset as $name => $vals)
        {
            $data = "";
            foreach($vals as $val)
                $data .= "<set value='{$val}' />";
            $xml .= "<dataset seriesName='{$name}'>{$data}</dataset>";
        }

        $data = "";
        foreach($categories as $val)
            $data .= "<category label='{$val}' />";
        $xml .= "<categories>{$data}</categories>";

        $xml .= "</chart>";
        return self::render($xml, '/charts/MSLine.swf', $width, $height);
    }

    /**
     * @static
     * @param array $dataset  具体数据，格式：array('name'=>val)
     * @param string|int $width
     * @param string|int $height
     * @param string $chartAttr
     * @return string
     */
    public static function renderPie3D($dataset, $width, $height, $chartAttr='')
    {
        $xml = "<chart $chartAttr formatNumberScale='0' showLabels='1' showValues='0'>";
        foreach($dataset as $name=>$val)
        {
            $xml .= "<set label='{$name}' value='{$val}' />";
        }
        $xml .= "</chart>";
        return self::render($xml, '/charts/Pie3D.swf', $width, $height);
    }

    /**
     * @static
     * @param $xml
     * @param $src
     * @param $width
     * @param $height
     * @return string
     */
    private static function render($xml, $src, $width, $height)
    {
        //$xml = str_replace("\n",'',$xml);
        self::$index++;
        $index = self::$index;

        /*$objects = '';
        $object_name = array('CAPTION','DATALABELS','DATAVALUES','SUBCAPTION','TOOLTIP','TRENDVALUES','XAXISNAME','YAXISNAME','YAXISVALUES');
        foreach($object_name as $name)
            $objects .= "<apply toObject='{$name}' styles='Font_0'/>";
        //全局字体 <style name="Font_1" type="font" font="Times" size="22" color="4F4F4F" bold="0" align="right" Italic="1" bgcolor="DFDFDF" bordercolor="D0D0D0" />
        $font = "<styles><definition><style name='Font_0' type='font' font='Arial' size='12'   /></definition><application>{$objects}</application></styles>";
        $xml = str_replace('</chart>',"{$font}</chart>",$xml);*/
        $xml = str_replace('<chart ', "<chart  baseFontSize='12' ", $xml);

        //注意：<param name="movie" value="$src?registerWithJS=1"/>一定要加，IE才能支持
        $html = <<<EOT
<object type="application/x-shockwave-flash" id="chartobject-{$index}" lang="EN" data="$src" width="$width" height="$height">
  <param name="scaleMode" value="noScale">
  <param name="scale" value="noScale">
  <param name="wMode" value="opaque">
  <param name="allowScriptAccess" value="always">
  <param name="quality" value="best">
  <param name="movie" value="$src?registerWithJS=1"/>
  <embed src="$src?registerWithJS=1" FlashVars="&chartWidth=$width&chartHeight=$height&debugMode=0&dataXML=$xml" quality="high" width="$width" height="$height" name="chartobject-{$index}" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
  <param name="flashvars" value="lang=EN&debugMode=undefined&scaleMode=noScale&animation=undefined&registerWithJS=1&chartWidth=$width&chartHeight=$height&InvalidXMLText=Invalid data.&dataXML=$xml">
</object>
EOT;
        return $html;
    }
}