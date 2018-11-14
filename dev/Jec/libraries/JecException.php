<?php
/**
 * @copyright Jec
 * @package Jec框架
 * @link 
 * @author jecelyin peng
 * @license 转载或修改请保留版权信息
 * Jec异常、错误处理类
 */

class JecException extends Exception
{

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
        self::doError($code, $message, '', 0, array());
    }

    /**
     * 处理错误
     * @param string $string 错误信息
     * @param int $code 错误代码
     * @param string $file 错误文件名
     * @param int $line 所在行数
     * @param array $context 错误内容
     * @return null
     */
    public static function doError($code, $string, $file, $line, $context)
    {
        if ($code == 0 && is_array($context) && !isset($context['class']) && !isset($context['GLOBALS'])) {
            unset($context['CONFIG']);
            $contexts = !$context ? null : var_export($context, 1);
        } elseif ($code != 0) {
            $context = array_reverse(debug_backtrace());
            $contexts = "\n";
            foreach ($context as $cval)
            {
                $contexts .= "File: {$cval['file']} [line:{$cval['line']}]";
                if (isset($cval['class']))
                    $contexts .= " ( {$cval['class']}{$cval['type']}{$cval['function']} )\n";
                else
                    $contexts .= " ( {$cval['function']} )\n";
            }
        } else {
            $contexts = null;
        }

        return self::showError($string, $code, $file, $line, $contexts);
    }

    /**
     * @static
     * 处理异常信息
     * @param Exception $e
     * @return null
     */
    public static function doException($e)
    {
        //if(!isset($e -> trace))
        //    $e -> trace = array();

        return self::showError($e->message, $e->code, $e->file, $e->line, $e->getTraceAsString());
    }

    /**
     * 显示错误
     * @param string $string 错误信息
     * @param int $code 错误代码
     * @param string $file 错误文件名
     * @param int $line 所在行数
     * @param array $context 错误内容
     * @param bool $show 非调试状态也显示
     * @param bool $exit 是否退出当前程序
     * @return null
     */
    public static function showError($string, $code, $file, $line, $context, $show = false, $exit = true)
    {
        global $CONFIG;
        //Undefined index
        if ($code == E_NOTICE || $code == E_USER_NOTICE)
            return;

        $str = '';

        if (is_array($context)) {
            unset($context['CONFIG']);
            unset($context['GLOBALS']);
            $context = var_export($context, true);
        }

        if (ERROR_LEVEL == ERROR_TO_FILE)
            $isLogtofile = true;
        else
            $isLogtofile = false;

        if (isCLI()) {
            $str .= "String: {$string}\n";
            if ($file)
                $str .= "File({$line}): {$file}\n";

            if ($code)
                $str .= "Code: {$code}\n";

            if ($context)
                $str .= "Context:\n{$context}\n";

        } else {
            //处理一下，不然在浏览器看<, &amp;这些字符时会有问题
            $string = htmlspecialchars($string);
            //尝试关闭文档，让错误直接显示出来
            $str .= '</body></html>';
            if ($isLogtofile)
                $css = '';
            else
                $css = 'position:absolute;left:0;top:0;';

            $charset = $CONFIG['html_charset'] ? $CONFIG['html_charset'] : 'utf-8';
            $str .= '<meta http-equiv="Content-Type" content="text/html;charset=' . $charset . '"/>';
            $str .= '<div id="js_ierror"><pre style="' . $css . 'clear:both;margin:10px auto;text-align:left;border:2px solid #F00;width:90%; background:#FF9;margin-top:24px; padding:10px; font-family:Fixedsys,mono; font-size:16px;word-break:break-all;word-wrap:break-word;">';
            $str .= '<div><strong>Time: </strong>' . date('Y-m-d H:i:s') . '</div>';
            $str .= '<div><strong>String: </strong>' . $string . '</div>';
            if ($file)
                $str .= "<strong>file: </strong>{$file} (<strong style=\"color:red;\">{$line}</strong>)<br/>";

            if ($code)
                $str .= "<strong>code: </strong>{$code}<br/>";

            if ($context)
                $str .= "<strong>context: </strong><br/>{$context}";

            $str .= '</pre></div>';

            /*if(!$isLogtofile)
                $str .= '<script type="text/javascript">document.body.innerHTML = document.getElementById("js_ierror").innerHTML</script>';*/
        }

        if ($isLogtofile)
            self::log($str);
        elseif ($show || ERROR_LEVEL != ERROR_NONE)
            echo $str;

        if ($exit)
            exit;
    }

    /**
     * 将错误信息写入日志文件
     * @param string $str
     * @return bool
     */
    public static function log($str)
    {
        if (ERROR_LEVEL != ERROR_NONE)
            echo $str;

        $subfix = isCLI() ? 'cli' : 'web';
        $file = VAR_PATH . '/log/i.error.'.$subfix.'.html';
        return file_put_contents($file, $str, FILE_APPEND);
    }

}//end class