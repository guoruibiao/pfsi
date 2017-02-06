<?php

/**
 * Created by PhpStorm.
 * User: ${郭璞}
 * Date: 2017/2/6
 * Time: 9:39
 * Description: PHP  Function Set Installer.
 */

/**
 * Class FunctionInfo
 * 通用函数类对象，将根据dependency.xml文件中的配置信息来生成。
 */
class FunctionInfo
{
    /**
     * @name  通用函数文件的函数名称
     */
    public $name;
    /**
     * @srcPath  通用函数文件在服务器端的存储位置，也是根据配置文件来决定。
     */
    public $srcPath;
    /**
     * @savedPath  通用函数文件将被保存到本地项目的文件位置。
     */
    public $savedPath;

    /**
     * FunctionInfo constructor.
     * @param $name
     * @param $srcPath
     * @param $savedPath
     */
    public function __construct($name, $srcPath, $savedPath)
    {
        $this->name = $name;
        $this->srcPath = $srcPath;
        $this->savedPath = $savedPath;
    }

    /**
     * 析构方法，销毁不再被使用的变量，释放内存空间。
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->name);
        unset($this->srcPath);
        unset($this->savedPath);
    }

    /**
     * 模拟Java中对于对象的打印输出。默认以字符串的形式存在！
     */
    public function toString()
    {
        echo sprintf("FunctionInfo: {'name': %s, 'srcPath': %s, 'savedPath': %s}", $this->name, $this->srcPath, $this->savedPath);
    }
}

/**
 * Class Downloader
 * 下载github上对应仓库中的可以被使用的函数集文件，并以数组的形式将其名称返回。
 */
class Downloader
{
    /**
     * @var array
     * 服务器支持的通用函数所组成的数组。
     */
    private $supportFunctionArray;

    /**
     * Downloader constructor.
     */
    public function __construct()
    {
        // 初始化数组
        $this->supportFunctionArray = array();
    }

    /**
     * 析构方法，销毁不再被使用的变量，释放内存空间。
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->supportFunctionArray);
    }

    /**
     * @return array
     *
     * 针对服务器上的配置信息，解析服务器支持的通用函数的集合，并以数组的形式返回。
     */
    public function download()
    {
        // 加载下载链接地址
        $url = "https://github.com/guoruibiao/pfsi/raw/master/dependency.xml";
        // 解析xml数据
        $dependencies = simplexml_load_string(file_get_contents($url), "SimpleXMLElement", LIBXML_NOCDATA);
        foreach ($dependencies as $dependency) {
            $functionInfo = new FunctionInfo((string)$dependency->name, (string)$dependency->srcPath,
                (string)$dependency->savedPath);
//            array_push($this->supportFunctionArray, (string)$dependency->name);
            array_push($this->supportFunctionArray, $functionInfo);
        }

//        var_dump($this->supportFunctionArray);


        // 处理解析数据并以数组形式返回
        return $this->supportFunctionArray;
    }

}

/**
 * Class DependencyParser
 *
 * 此类主要是处理本地配置文件中与服务器端所支持的函数依赖中的公共部分和不支持的部分的解析。
 */
class DependencyParser
{
    /**
     * @var array
     * 服务器支持的可被下载的通用函数文件数组。
     */
    private $canBeDownload;

    /**
     * @var array
     * 服务器不支持的可被下载的通用函数文件数组。
     */
    private $canNotBeDownload;

    /**
     * DependencyParser constructor.
     */
    public function __construct()
    {
        $this->canBeDownload = array();
        $this->canNotBeDownload = array();
    }

    /**
     * 销毁不再被使用的变量，释放内存空间。
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->canBeDownload);
        unset($this->canNotBeDownload);
    }


    /**
     * 解析本地配置文件，将解析结果通过两个数组的形式返回。
     */
    public function parse()
    {
        // 本地依赖函数集解析，处理结果反馈给一个临时数组
        $localDependencies = simplexml_load_file('./dependency.xml', "SimpleXMLElement", LIBXML_NOCDATA);
        $localDependenciesObjectArray = array();
        foreach ($localDependencies as $dependency) {
            $functionInfo = new FunctionInfo((string)$dependency->name, (string)$dependency->srcPath,
                (string)$dependency->savedPath);
            array_push($localDependenciesObjectArray, $functionInfo);
        }


        // 获取服务器上可供下载依赖集，也反馈给一个临时数组
        $downloader = new Downloader();
        $serverDependenciesObjectArray = $downloader->download();
        unset($downloader);

        // 对比本地与服务器上依赖集，过滤后反馈给本类的两个成员数组变量
        foreach ($localDependenciesObjectArray as $item) {
            if (in_array($item, $serverDependenciesObjectArray)) {
                array_push($this->canBeDownload, $item);
            } else {
                array_push($this->canNotBeDownload, $item);
            }
        }

        // 销毁无用对象，释放内存占用
        unset($serverDependenciesObjectArray);
        unset($localDependenciesObjectArray);

//        var_dump($this->canBeDownload);
//        echo "-------------------------------------<br />";
//        var_dump($this->canNotBeDownload);

    }


    /**
     * @return array
     * 返回给调用方服务器所支持的通用函数的集合。
     */
    public function getCanBeDownload()
    {
        return $this->canBeDownload;
    }

    /**
     * @return array
     * 返回给调用方服务器所不支持的通用函数的集合。
     */
    public function getCanNotBeDownload()
    {
        return $this->canNotBeDownload;
    }
}


/**
 * Class Handler
 * 一方面将可以被下载的函数集和不可被下载的函数集， 以优雅的方式展示给前台用户；
 *
 * 另一方面将可被下载的函数集按照配置文件中设置好的路径下载到本地项目。
 */
class Handler
{

    /**
     * @var array
     * 服务器所支持的通用函数的集合。
     */
    private $canBeDownload;
    /**
     * @var array
     * 服务器所不支持的通用函数的集合。
     */
    private $canNotBeDownload;

    /**
     * Handler constructor.
     *
     * 实例化依赖解析器，将处理结果反馈给两个成员变量。并适时的销毁不再被使用到的变量。
     */
    public function __construct()
    {
        $dependencyParser = new DependencyParser();
        $dependencyParser->parse();
        $this->canBeDownload = $dependencyParser->getCanBeDownload();
        $this->canNotBeDownload = $dependencyParser->getCanNotBeDownload();

        unset($dependencyParser);
    }

    /**
     * 析构函数，销毁不再被使用的变量，释放内存空间。
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->canBeDownload);
        unset($this->canNotBeDownload);
    }

    /**
     * 由用户主动调用，或者在handle方法中被调用，生成对比文件。
     */
    public function notice() {


        global $ltable;
        $canlength = count($this->canBeDownload);
//        var_dump($canlength);


        for ($index=0; $index<$canlength; $index++) {
            $type = $index%2==0?"odd":"even";
            $ltable .="<tr class='".$type."'>";
            $ltable.="<td>".($index+1)."</td>";
            $ltable.="<td>".$this->canBeDownload[$index]->name."</td>";
            $ltable.="<td>".$this->canBeDownload[$index]->srcPath."</td>";
            $ltable.="<td>".$this->canBeDownload[$index]->savedPath."</td>";
            $ltable.="</tr>";
        }

        /////////////////////////////////////////////////////////////////////
        // 测试 左边表格内容生成情况
        /////////////////////////////////////////////////////////////////////
//        var_dump($ltable);

        global $rtable;
        $cannotlength = count($this->canNotBeDownload);
        for($index=0; $index<$cannotlength; $index++) {
            $type = $index%2==0?"odd":"even";
            $rtable.="<tr class='".$type."'>";
            $rtable.="<td>".($index+1)."</td>";
            $rtable.="<td>".$this->canNotBeDownload[$index]->name."</td>";
            $rtable.="<td>".$this->canNotBeDownload[$index]->srcPath."</td>";
            $rtable.="<td>".$this->canNotBeDownload[$index]->savedPath."</td>";
            $rtable.="</tr>";
        }

        $diffstr = <<<ENDTAG
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>依赖函数集对照表</title>
    <style>
        * {
            padding:0px;
            margin:0px;
        }

        h2, h3 {
            height: 28px;
            text-align: center;
        }
        
        .container {
            width: 1080px;
            height:auto;
            margin:0px auto;
            background: #e6e6e6;
        }

        .left {
            padding: 3px;
            width:532px;
            height:auto;
            background: lightblue;
            float: left;
            border-right: 1px solid hotpink;
        }

        .right {
            padding:3px;
            width:532px;
            height:auto;
            background: lightgreen;
            float: right;
            border-left: 1px solid deepskyblue;
        }
        table ,td {
            margin-left: 28px;
            border: 1px solid #000 ;
        }
        tr:hover {
            background: #FFF;
        }

        .ltable {
            width: 90%;
            height:100%;

            padding: 2px;
        }

        .rtable {
            width: 90%;
            height:100%;

            padding: 2px;
        }

        .odd {
            background-color: #6b9cde;
        }

        .even {
            background-color: #3c8b3c;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>PHP Function Set Installer 处理清单</h2>
    <div class="left">
        <h3>可下载函数依赖集</h3>
        <div class="ltable">
            <table>
                <thead>
                <tr>
                    <td>序号</td>
                    <td>名称</td>
                    <td>在服务器上位置</td>
                    <td>本地存储位置</td>
                </tr>
                </thead>
                <tbody>
                    {$ltable}
                </tbody>
            </table>
        </div>
    </div>
    <div class="right">
        <h3>不支持的函数依赖集</h3>
        <div class="rtable">
            <table>
                <thead>
                <tr>
                    <td>序号</td>
                    <td>名称</td>
                    <td>在服务器上位置</td>
                    <td>本地存储位置</td>
                </tr>
                </thead>
                <tbody>
                {$rtable}
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
ENDTAG;

        file_put_contents('./result.html', $diffstr);
        echo "<script>alert('服务器支持的函数集情况详见本级目录下的result.html文件');</script>";

        // 销毁变量
        unset($ltable);
        unset($rtable);
        unset($diffstr);

    }

    /**
     * 下载并保存服务器支持的通用函数集合。
     */
    private function downloadAndSave() {
        // 处理服务器支持的函数集的下载与本地保存工作
        foreach ($this->canBeDownload as $item) {
            // 拼接函数文件在服务器上的下载地址。并进行下载处理
            $url = "https://github.com/guoruibiao/pfsi/raw/master".$item->srcPath;
            $filecontent = file_get_contents($url);
            // 判断本地文件夹是否存在，不存在则创建。默认创建位置为pfsi.php同级目录
            $this->isDirExists($this->getSavedDir($item->savedPath));
            $filepath = "./commons/".$item->name.".php";

            // 完善处理，保存下载好的函数集
            file_put_contents($filepath, $filecontent);
        }

        echo "<script>alert('服务器支持的函数集已经下载完毕，详情请在本地项目目录中进行查看！');</script>";
    }

    /**
     * @param $path
     * @return string
     * 获取通用函数文件将在本地项目中保存的文件夹的位置，默认为与pfsi.php同级目录下的commons文件夹。
     */
    private function getSavedDir($path) {
        if (isset($path)) {
            $path = ltrim($path, "/");
            $arr = explode("/", $path);
            $path = implode("/", array_slice($arr, 0, count($arr)-1));
            unset($arr);
            return $path;
        }
        return "commons";
    }

    /**
     * @param $dirname
     * 判断给定文件夹是否存在, 如果不存在则创建此文件夹
     */
    private function isDirExists($dirname) {
        if(!file_exists($dirname))
            mkdir($dirname);
    }

    /**
     * 对外提供的功用方法api。完成整个功能的集成。
     */
    public function handle() {
        // 预处理，通知用户服务器支持的函数集有哪些，美化效果可在result.html中查看！
        $this->notice();

        // 下载 与 保存 函数依赖集
        $this->downloadAndSave();

    }

}

//////////////////////////////////////////////////////////////
// 正式使用入口
//////////////////////////////////////////////////////////////
$handler = new Handler();
$handler->handle();
