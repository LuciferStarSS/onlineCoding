<?php
$type=isset($_GET['t'])?$_GET['t']:"";
$type=$type==""?"c":$type;

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
    <title>WebDevTools</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="./static/devc.css">
    <script src=./static/js/jquery.js></script>
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
        <div style="position: relative;top: 19px;height: 2%;" id="title-result" class="head-section">运行结果</div>
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
   $.post("http://192.168.3.112:81/run<?php echo $type;?>", { "code": editor.getValue(),"data":$("#inputdata")[0].value, "args":$("#inputargs")[0].value}, function (data) {
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
