<?php
/**
 * Created by PhpStorm.
 * User: ${郭璞}
 * Date: 2017/2/3
 * Time: 9:32
 * Description: 读取给定目录及子目录下文件路径信息
 */

class FileWatcher{
    public $fileinfo;
    /**
     * FileWatcher constructor.
     * @param $path 给定路径
     */
    function __construct(){
        $this->fileinfo = array();
    }

    /**
     * 去除路径设置信息，析构方法
     */
    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->fileinfo = null;
    }

    public function scanDir($path) {
        if(is_dir($path)) {
            $tmpdir = dir($path);
            while($tmpfile = $tmpdir->read()) {
                if($tmpfile!='.' && $tmpfile!='..')
                    $this->scanDir($path."/".$tmpfile);
            }
            $tmpdir->close();
        }
        if(is_file($path)) {
            array_push($this->fileinfo, $path);
        }
        return $this->fileinfo;
    }


}

class FileInfo{
    public $level;
    public $pathstep;
    public $fullpath;

    public function __construct()
    {
        //pathstep 存储当前路径经过的文件夹信息
        $this->pathstep = array();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->pathstep = null;
        $this->level = null;
        $this->fullpath = null;
    }

}

class PathParser{
    private $patharray;

    private $resultSet;


    public function __construct($patharray)
    {
        // 从外部获取到处理结果集
        $this->patharray = $patharray;
        // 初始化结果集数组
        $this->resultSet = array();
        // bean类对象
        $this->fileinfo = new FileInfo();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->resultSet = null;
        $this->level = null;
        $this->fullpath = null;
    }

    public function parse() {
        for ($index=0; $index<count($this->patharray); $index++) {
            // 赋予完整路径
            $fileinfo = new FileInfo();
            $fileinfo->fullpath = $this->patharray[$index];
            //计算level
            $fileinfo->level = $this->parseLavel($fileinfo->fullpath);
//            echo $fileinfo->level."<------->";

            // 计算经过的路径并进行存储
            $fileinfo->pathstep = $this->parseStep($fileinfo->fullpath);
//            echo $fileinfo->pathstep."<br />";

//            var_dump($fileinfo->pathstep);

            array_push($this->resultSet, $fileinfo);


        }
        //返回计算结果，整体作为结果集返回
        return $this->resultSet;
    }

    /**
     * 获取给定路径所经过的路径的结果集，将用于分级目录展示
     * @param $fileinfo
     * @return int
     */
    public function parseStep($fileinfo) {
        if(!$fileinfo) {
            echo "<mark>".$fileinfo." path error!</mark>";
            exit();
        }
        // 判断是否为相对路径是的话去掉第一级目录。 啊好烦，windows上和linux上差别还这么大，怎么处理好呢。。。
        // 还是按照文件在服务器上的位置来进行来处理好了。判断是不是相对路径然后再针对“路径分隔符”计算路径的level
        if($this->isRelativePath($fileinfo) == 1) {
            // 相对路径处理
            // 去掉相对路径符号
            $fileinfo = substr($fileinfo,2, strlen($fileinfo));

            // 按照目录分隔符 作为切割标准，结果就是路径本身包含的路径信息
            return explode("/", $fileinfo);
        }else if($this->isRelativePath($fileinfo) == 2){
            $fileinfo = substr($fileinfo, 3, strlen($fileinfo));
            return explode("/", $fileinfo);
        }else if ($this->isAbsolutePath($fileinfo)) {
            // 绝对路径处理
        }else{
            // 文件路径非法
            echo "<mark>".$fileinfo." 文件路径非法</mark>";
            exit();
        }
    }

    public function parseLavel($fileinfo) {
        if(!$fileinfo) {
            echo "<mark>".$fileinfo." path error!</mark>";
            exit();
        }
        // 判断是否为相对路径是的话去掉第一级目录。 啊好烦，windows上和linux上差别还这么大，怎么处理好呢。。。
        // 还是按照文件在服务器上的位置来进行来处理好了。判断是不是相对路径然后再针对“路径分隔符”计算路径的level
        if($this->isRelativePath($fileinfo) == 1) {
            // 相对路径处理
            // 去掉当前相对路径符号
            $fileinfo = substr($fileinfo,2, strlen($fileinfo));
            // 通过计算 路径分隔符来作为level的判断标准
//            echo "<mark>".count(explode("/", $fileinfo))."</mark>";
            return count(explode("/", $fileinfo));
        }else if ($this->isRelativePath($fileinfo) == 2 ) {
            //去掉父级目录信息
            $fileinfo = substr($fileinfo, 3, strlen($fileinfo));
            return count(explode("/", $fileinfo));
        }else if ($this->isAbsolutePath($fileinfo)) {
            // 绝对路径处理
        }else{
            // 文件路径非法
            echo "<mark>".$fileinfo." 文件路径非法</mark>";
            exit();
        }


    }

    /**
     * 判断是否为相对路径
     * @param $path
     * @return bool
     *
     */
    private function isRelativePath($path) {
        // 父级目录拥有更高的优先级
        $prefix = substr($path, 0, 3);
        if($prefix == "../") {
            return 2;
        }

        // 处理 当前目录情况
        $prefix = substr($path, 0, 2);
        if ($prefix == "./"){
            return 1;
        }else{
            return false;
        }
    }

    /**
     * 判断给定路径是否为绝对路径
     * @param $path
     * @return bool
     */
    private function isAbsolutePath($path) {
        $prefix = substr($path, 0, 1);
        if($prefix=="/"){
            return true;
        }else{
            return false;
        }
    }


}

////////////////////////////////////////////////测试用例

//获取全部文件以及路径信息
//$fileWatcher = new FileWatcher();
//$result = $fileWatcher->scanDir('.');
//
//
//$pathParser = new PathParser($result);
//$resultSet = $pathParser->parse();
//echo json_encode($resultSet);
//echo $resultSet;