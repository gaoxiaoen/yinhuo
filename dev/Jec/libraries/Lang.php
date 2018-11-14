<?php
/**
 *@copyright Jec
 *@package Jec框架
 *@link 
 *@author jecelyin peng
 *@license 转载或修改请保留版权信息
 *
 * 多语言化处理
 *
 * 初始化后可以使用 _('str') 来实现字符串多语言
 * 制作多语言文件方法:
 * 提取需要翻译的字符串,非utf-8编码需要使用--from-code参数来指定
 * $ xgettext --from-code=gbk -n *.php -o myapp.po
 * 得到的 myapp.po 文件为 UTF-8
 * 转换到非utf-8编码下工作,不然使用_("str")输出后会乱码
 * $ msgconv --to-code=gbk myapp.po -o myapp.po
 * 生成.mo文件
 * $ msgfmt -o Jec.mo myapp.po
 * #############################################
 * 注意如果无法正常切换语言，需要修改/var/lib/locales/supported.d/locale文件（UBUNTU下）
 * 添加你需要支持的语言
 * 可能需要运行dpkg-reconfigure locales
 * #############################################
 * mo文件会给php缓存起来，修改后记得重启php进程
 */

class Lang
{
    /**
     * @static
     * 初始化多语言模块
     */
    public static function init()
    {
        global $CONFIG;

        $lang = $CONFIG['lang'];
        if(!$lang)
        {
            $lang = array(
                'locale' => 'zh_CN',
                'encoding' => 'utf-8', //语言文件编码
            );
        }
        putenv('LC_ALL='.$lang['locale']);
        putenv("LANGUAGE={$lang['locale']}");
        putenv("LANG={$lang['locale']}");

        setlocale(LC_ALL, $lang['locale'] . ".utf8",
                        $lang['locale'] . ".UTF8",
                        $lang['locale'] . ".utf-8",
                        $lang['locale'] . ".UTF-8",
                        $lang['locale']);

        // 翻译文件必须在路径： locale/zh_CN/LC_MESSAGES/myAppPhp.mo
        bindtextdomain($CONFIG['app_name'], APP_PATH . "/locale");
        bind_textdomain_codeset($CONFIG['app_name'], $lang['encoding']);

        // Choose domain
        textdomain($CONFIG['app_name']);

    }
}