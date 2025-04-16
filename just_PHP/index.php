<?php
$type=isset($_GET['t'])?$_GET['t']:"";
$type=$type==""?"c":$type;

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit("请先登录。");
$classid=isset($_COOKIE["CLASSID"])?$_COOKIE["CLASSID"]:exit("请先登录。");
$gradeid=isset($_COOKIE["GRADEID"])?$_COOKIE["GRADEID"]:-1;
$projname=isset($_COOKIE["PRJNAME"])?$_COOKIE["PRJNAME"]:"未命名";
$datedir=isset($_COOKIE["DD"])?$_COOKIE["DD"]:"";



//$username="test";
$projname="第一个程序";
include("../include/config.inc.php");

$CN=isset($classname[$classid-1])?$classname[$classid-1]:exit("错误的班级信息。");

if($gradeid==-1)
{
   $gradeid=$grades[$classid];
}

$demo_source=Array(
"c"=>'#include <stdio.h>

int main(int argc, char **argv)
{
    printf("Hello C World!!\\n");
    return 0;
}',
"cpp"=>'#include <iostream>

using namespace std;

int main(int argc, char **argv)
{
    cout << "Hello C++ World" << endl;
    return 0;
}',
"py"=>'import sys
import os

if __name__ == "__main__":
    print ("Hello Python World!!")
',
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>在线编程系统</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="./static/devc.css">
    <script src=./static/js/jquery.js></script>
    <script>
       var username='<?php echo $username;?>';
       var classid='<?php echo $classid;?>';
       var gradeid='<?php echo $gradeid;?>';
       var projname='<?php echo $projname;?>';
       var datedir='<?php echo $datedir;?>';
    </script>
</head>
<body id="index" class="home">
<div id="links">
<ul>
<li><a href="?t=">C Code</a></li>
<li><a href="?t=cpp">C++ Code</a></li>
<li><a href="?t=py">Python Code</a></li>
</ul>
</div>
<div id="content">
    <!-- ************  CODING ZONE  ************ -->
    <form method="post" action="?a=runc">
    <div id="code">
            <div id="title-code" class="head-section">
              源代码
            </div>

            
<textarea id="text-code" name="code" rows=15 cols=60>
<?php
echo htmlspecialchars($demo_source[$type]);
?>
</textarea><br/>
<div id="text-code-ace" class="hidden"><?php
echo htmlspecialchars($demo_source[$type]);
?> 
</div>


    </div>
    
    <!-- ************ RUNNING ZONE RESULTS ************ -->
    
    <div id="result">
        <div style="position: relative;top: 0px;height: 5%;font-weight: bold;">命令行参数：<input style="position: absolute;left: 90px;width: 270px;" id="inputargs" class="head-section" /></div>
        <div style="position: relative;top: 6px;height: 6%;font-weight: bold;">模拟输入：<textarea  style="position: absolute;left: 90px;width: 272px;" id="inputdata" class="head-section" /></textarea></div>
        <input style="position: absolute;right: 121px;left: 380px;top: 19px;height: 50px;width: 60px;;"id="launch-button" class="head-section" type=button onclick="goLaunching()" value="运行" />
        <div style="position: relative;top: 19px;height: 2%;left:0px" id="title-result" class="head-section">运行结果</div>
        <textarea style=" height: 69%;   position: relative;top: 42px;" id="text-result" rows="18" cols="70" readonly>无输出</textarea>
    </div>
    </form>
    <!-- ************ COMPILATION RESULTS ZONE ************ -->
    
    <div id="compile">
        <div id="title-compile" class="head-section">
            编译/输出信息
        </div>
        
        <textarea id="text-compile" rows="7" cols="140" readonly></textarea>

    </div>
</div>
<script src="./static/ace/ace.js"></script>
<script>
    // The Ace editor needs divs instead of textareas
    // So we hide the textarea and show a div instead
    var editorElement = document.getElementById("text-code");
    editorElement.classList.add("hidden");
    document.getElementById("text-code-ace").classList.remove("hidden");

    // Set up the editor
    var editor = ace.edit("text-code-ace");
    editor.setTheme("ace/theme/tomorrow");
    var language = ("runc" === "runpy") ? "python" : "c_cpp";
    editor.getSession().setMode("ace/mode/" + language);

    // Make sure we copy the content of the editor to the textarea before posting
    // its content to the server
    document.getElementById("launch-button").addEventListener("click", function () {
        editorElement.innerHTML = editor.getValue();
    });
</script>

<script>
function goLaunching()
{
   var username='<?php echo $username;?>';
   var classid='<?php echo $classid;?>';
   var classname='<?php echo $CN;?>';
   var gradeid='<?php echo $gradeid;?>';
   var projname='<?php echo $projname;?>';
   var datedir='<?php echo $datedir;?>';
   var data=$("#inputdata")[0].value;
   var args=$("#inputargs")[0].value;

   $("#text-result").text("");
   $("#text-compile").text("");
   
   $.post("cc.php?t=<?php echo $type;?>", {"UN":username,"CID":classname,"PN":projname, "DD":datedir, "CODE": editor.getValue(),"DATA":data, "ARGS":args}, function (data) {
      data=data.replaceAll("\r","\\r");
      data=data.replaceAll("\n","\\n");
      var jsonData=JSON.parse(data);
      if(jsonData)
      {
         $("#text-result").text(jsonData.resrun);

         $("#text-compile").text(jsonData.rescomp);
      }

   });
}
</script>
</body>
</html>
