<?php
   $type=isset($_GET['t'])?$_GET['t']:"";
   $type=$type==""?"c":$type;
$classid=isset($_COOKIE["CLASSID"])?$_COOKIE["CLASSID"]:"";
$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:header("Location: /class/");
include "../include/config.inc.php";

$strCN="";
if(isset($classname[$classid]))
   $strCN=$classname[$classid];

$room=1;
//$admin=1;

$admin=0;

if(isset($scratch_class[$username])) $admin=1;


?><!DOCTYPE html>
<html lang=zh-cn>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=IE10">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="content-type" content="text/html;charset=UTF-8">
<title>在线编程平台</title>
<link rel="stylesheet" type="text/css" href="./static/devc.css">
<script>
   var username='<?php echo $username;?>';
   var classname='<?php echo $strCN;?>';
   var ext='';//<?php echo $type;?>';
   var taskID=0;
</script>
<script src=./static/js/jquery.js></script>
</head>
<body>
<div align=center>
<div style="width: 100%;max-width: 1338px;margin-left:5px">
  <div align=center>
    <div alignstyle="0" style="border-radius: 3%;width: 100%;min-width:666px;height: 90px;">
      <div  align="center" style="font-size:20;width:100%;overflow:auto;"><h1>C/C++/Python编程平台</h1></div>
    </div>
    <div style="float:left;width: 100%;max-width: 1338px; margin-top:0px;margin-bottom:10px;min-width: 650px;">
      <div style="background:darkgray;height: 21px;">
        <select id=class onchange="getClass(this.value)" title="教师可巡查多个班级，学生只显示自己的班级。" style="position: relative;left: -210px;"><option value="">请选择班级</option>
<?php
if($admin)
{
   for($i=0;$i<count($class_room[$room]);$i++)
   {
      echo "<option value=\"".$classname[$class_room[$room][$i]-1]."\">".$classname[$class_room[$room][$i]-1]."</option>";
   }
}
else
   echo "<option value=\"".$strCN."\">".$strCN."</option>";
?>
          </select>
        <div style="position: relative;top: -20px;width:270px;height: 320px;left:-28px">
           <input type=text id=INPUT_TKID style="position: absolute;left:0px;visibility:hidden;width:60px" onclick="showTaskList()" placeholder="任务编号" title="输入数字后敲回车键可直接打开">
           <div id=TASKLIST style="position: absolute;left: 0px;top: 25px;height:200px;visibility:hidden;z-index: 10;overflow: overlay;max-height: 170px;background: chocolate;"></div>
         </div>
        <!--select id=TKID onclick="getTasks(this.value);" style="visibility:hidden" title="不同的年级可以显示不同的试题。"><option value="">请选择练习题</option></select-->
        <select id=EXT onchange="getFile(this.value,0);" style="visibility: hidden;position: relative;top: -340px;left: 34px;" title="如果之前提交过某一语言的源码，则可以直接打开。"><option value="">请选择语言</option><option value="c">C</option><option value="cpp">C++</option><option value="py">Python</option></select>
        <select id=SWORKS onchange="getFile(this.value,1);" style="visibility: hidden;position: relative;top: -340px;left: 32px;width:158px" title="如果之前提交过某一语言的源码，则可以直接打开。"><option value="">请选择源码文件</option></select>
      </div>
    </div>

    <!--左侧-->
    <div style="float:left;width: 666px;height:705px;margin-right: 6px;margin-bottom: 10px;background:beige ;/* margin-left: 10px; */">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <!--预设任务分发-->
        <div style="text-align: left;">问题：</div>
        <textarea id=question readonly></textarea>
        <div style="position: absolute;width: 300px;">
          <div style="text-align: left;">输入样例：</div>
          <textarea type=text id="demoinput" readonly></textarea>
        </div>
        <div   style="position: absolute;width: 300px;left: 331px;">
          <div style="text-align: left;">输出样例：</div>
          <textarea readonly id="demooutput"></textarea>
        </div>
        <!--预设任务分发-->
        <div style="position: relative;top: 200px;">
          <div style="text-align: left;">操作说明：<br>
            1.依次选择班级、任务和编程语言种类后，如果之前提交过代码，则会自动调取并显示代码；<br>
            2.代码编写完成后，在运行前，可以根据需要设置“执行参数”和“数据输入”；<br>
            3.可以根据“编译结果”排查代码中的错误；<br>
            4.如果代码正确，且程序有结果输出，则会显示在“输出结果”中；<br>
            5.每个程序最多执行3秒钟，如算法过于复杂而超时，将不能得到正确的结果。</div>
        </div>
      </div>
    </div>
    <!--左侧-->

    <!--右侧-->
    <div alignstyle="0" style="float:left;width: 666px;height: 705px;position:relative;background: beige;">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <div style="text-align: left;">源代码：</div>
        <textarea id="text-code"></textarea>
        <div id="text-code-ace" class="hidden"></div>
        <div style="width: 310px;top: 432px;/* left: 50px; */position:absolute;">
          <div style="text-align: left;">执行参数：</div>
          <input type=text id="inputargs">
          <div style="text-align: left;">数据输入：</div>
          <textarea  id="inputdata"></textarea>
          <input type=button id="launch-button" value="保存&测试" onclick="checkCode();" style="position: relative;top: 5px;width:305px;height: 30px;font-size: 14px;font-weight: bolder;">
          <input type=button id="launch-button" value="判分" onclick="evaluateCode();" style="position: relative;top: 14px;width: 305px;height: 35px;font-size: 18px;font-weight: bolder;">
        </div>
        <div style="width: 310px;left: 318px;top: 432px;position: absolute;">
          <div style="text-align: left;">编译结果：</div>
          <textarea id="compile" readonly></textarea>
          <div style="text-align: left;">输出结果：</div>
          <textarea id="result" readonly></textarea>
        </div>
      </div>
    </div>
    <!--右侧-->
  </div>
<!--其它-->
  <div style="float:left;width: 100%;max-width: 1338px; margin-top:10px; /* margin-right: 650px; */ margin-bottom:10px; min-width: 650px;">
     <div style="background:darkgray;text-align:center">Copyright</div>
  </div>
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
    var language = ("<?php echo $type;?>" === "py") ? "python" : "c_cpp";
    editor.getSession().setMode("ace/mode/" + language);

    // Make sure we copy the content of the editor to the textarea before posting
    // its content to the server
    document.getElementById("launch-button").addEventListener("click", function () {
        editorElement.innerHTML = editor.getValue();
    });
</script>

<script>

//响应回车
document.addEventListener('keydown',function (event){
   if(event.keyCode == 13){
      if(document.getElementById("INPUT_TKID").value)
         getTasks(document.getElementById("INPUT_TKID").value);
   }
});


function hideTaskList()
{
   var oTASKLIST=document.getElementById("TASKLIST").style.visibility="hidden";
}

function showTaskList()
{
   var oTASKLIST=document.getElementById("TASKLIST");

   if(oTASKLIST.style.visibility=="visible")
      oTASKLIST.style.visibility="hidden";
   else
      oTASKLIST.style.visibility="visible";
   //oTASKLIST.style.visibility="visible";
   oTASKLIST.innerHTML="";
   getTasks("");
}

//整理界面
//界面初始化
function doTestCleaning()
{
   document.getElementById("compile").value="";
   document.getElementById("result").value="";
}

function doTaskCleaning()
{
   doTestCleaning();
   document.getElementById("question").value="";
   document.getElementById("demoinput").value="";
   document.getElementById("demooutput").value="";
}



//保存、编译和运行源代码
function checkCode()
{
   var data=document.getElementById("inputdata").value;
   var args=document.getElementById("inputargs").value;
   var strCode=editor.getValue();

   if(strCode=="") alert("未输入代码，无需保存。");
   else if( data=="" && ( strCode.indexOf("cin")>0 || strCode.indexOf("scanf(")>0 || strCode.indexOf("sys.stdin")>0 ))
   {
      document.getElementById("inputdata").focus();
      alert("代码中有用户输入的处理，请配置相应的运行参数。");
   }
   else
   {
      doTestCleaning();
   
      $.post("./opt/cc.php?t="+ext, {"UN":username,"CN":classname,"TKID":taskID, "CODE": strCode,"DATA":data, "ARGS":args}, function (data) {
         var dataArr=data.split("<+-NOJSON-+>");
         if(dataArr.length==3)
         {
            document.getElementById("result").value=dataArr[1];
            document.getElementById("compile").value=dataArr[2];
         }
      });
   }
}

//判分
function evaluateCode()
{
   var data=document.getElementById("inputdata").value;
   var args=document.getElementById("inputargs").value;

   if(editor.getValue()=="") alert("未输入代码，无需保存。");

   doTestCleaning();
   
   $.post("./opt/ec.php?t="+ext, {"UN":username,"CN":classname,"TKID":taskID, "CODE": editor.getValue(),"DATA":data, "ARGS":args}, function (data) {
      var dataArr=data.split("<+-NOJSON-+>");
      if(dataArr.length==2)
      {
         document.getElementById("result").value=dataArr[1];
         //document.getElementById("compile").value=dataArr[2];
      }
   });
}

//获取任务列表
function getTasks(value)
{
   if(value=="") 
   {
      $.post("./opt/getTask.php?act=all", {}, function (data) {
         var jsonData=JSON.parse(data);
         if(jsonData)
         {
            //doTestCleaning();
            var oTASKLIST=document.getElementById("TASKLIST");
            var arrKeys=Object.keys(jsonData);
            for(i=0;i<arrKeys.length;i++)
            {
               var oDiv=document.createElement("DIV");
               oDiv.innerText=jsonData[arrKeys[i]];
               oDiv.className="listcontent";
               oDiv.onclick=function(){
                  getTasks(this.innerText);
               }
               oTASKLIST.append(oDiv);
            }
         }
      });
   }
   else if(value!=taskID)
   {
      editor.setValue("");
      taskID=value;
      document.getElementById("EXT").style.visibility="visible";
      getTask(taskID);
      if(ext)
         getFile(ext);
   }
}


//获取单个任务的配置信息
function getTask(value)
{
   if(value) 
   {
      document.getElementById("INPUT_TKID").value=value;
      $.post("./opt/getTask.php?act=get", {"TKID":value}, function (data) {
         var dataArr=data.split("<+-NOJSON-+>");
         if(dataArr.length==3)
         {
            document.getElementById("TASKLIST").style.visibility="hidden";
            doTestCleaning();
            document.getElementById("question").value=dataArr[0];
            document.getElementById("demoinput").value=dataArr[1];
            document.getElementById("demooutput").value=dataArr[2];
         }
         else
         {
            doTaskCleaning();
            alert(data);
         }
      });
   }
}


//选好班级，显示下一级下拉选择菜单
function getClass(value)
{
   if(value){
      classname=value;
      document.getElementById("INPUT_TKID").style.visibility="visible";
      document.getElementById("EXT").style.visibility="visible";
      if(taskID && ext)
      {
         getFile(ext);
      }
   }
}

//获取提交过的源代码数据
//如果是教师，则应该获取所有人的源代码的列表。
function getFile(value,f)
{
   if(value){
      hideTaskList();
      doTestCleaning();
      editor.setValue("");
      if(f!==1)
         ext=value;
      if(taskID)
         document.getElementById("INPUT_TKID").value=taskID;

<?php 
   if($admin)		//教师获取列表
   {
?>
      if(f)
      {
         $.post("./opt/getCode.php", {"UN":username, "CN":classname, "TKID":taskID, "L":ext,"FN":value}, function (data) {
            if(data)
            {
               if(data=="请登陆")
                  alert("登录已超时，请重新登录。");
               else
                  //editor.setValue(data);
                  typeWriter(data, "textElement", 100);
            }
         });

      }
      else
      {
            $.post("./opt/getCode.php?T="+value, {"UN":username, "CN":classname, "TKID":taskID, "L":ext}, function (data) {
            //var jsonData=JSON.parse(data);
            var oLIST=document.getElementById("SWORKS");
            oLIST.style.visibility="visible";
            oLIST.length=0;
            oLIST.append(new Option("请选择源码文件",""));
            for(var i=0;i<data.length;i++)
            {
               oLIST.append(new Option(data[i],data[i]));
            }
         },"json");
      }
<?php 
   }
   else			//学生获取文件
   {
?>
      $.post("./opt/getCode.php", {"UN":username, "CN":classname, "TKID":taskID, "L":ext}, function (data) {
         if(data)
         {
            if(data=="请登陆")
               alert("登录已超时，请重新登录。");
            else
               //editor.setValue(data);
               typeWriter(data, "textElement", 100);
         }
      });

<?php 
   }
?>
   }
}


function typeWriter(text, elementId, interval) {
    let i = 0;
    const element = document.createElement("div");
    const writeText = () => {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            editor.setValue(element.textContent);
            i++;
            requestAnimationFrame(writeText); // 使用requestAnimationFrame递归调用
        }
    };
    writeText();
}
 
// 使用示例
 // 每隔大约16.67毫秒（浏览器帧率通常为60Hz）显示下一个字符，但这里我们忽略了间隔的概念，因为requestAnimationFrame本身就是为了平滑动画设计的。



</script>
</body>
</html>
