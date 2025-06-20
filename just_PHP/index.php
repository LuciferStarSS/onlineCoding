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
   var admin=<?php echo $admin;?>;
   var taskID=0;
   var room=<?php echo $room;?>;
   var gradeid=taskID;		//post用。
   var GID=taskID;
   var lastID=0;		//websocket用。
   var dataCollecting=true;
   var bQuizStopped=false;
 
</script>
<script src=./static/js/jquery.js></script>
<script src=./js/indexDB.js></script>
</head>
<body>
<!--main-->
<div style="display: flex;justify-content: center; align-items: center; ">
<div align=center style="width: 100%;max-width: 1338px;margin-left:5px">
<div style="width: 100%;max-width: 1338px;margin-left:5px">
  <div align=center>
    <div alignstyle="0" style="border-radius: 3%;width: 100%;min-width:666px;height: 90px;">
      <div  align="center" style="font-size:20;width:100%;overflow:auto;"><h1>在线编程平台</h1></div>
    </div>
    <div style="float:left;width: 100%;max-width: 1338px; margin-top:0px;margin-bottom:10px;min-width: 666px;">
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
        <select id=EXT onchange="getFile(this.value,0);" style="visibility: hidden;position: relative;top: -340px;left: 34px;" title="如果之前提交过某一语言的源码，则可以直接打开。">
<option value="">请选择语言</option>
<option value="c">C</option>
<option value="cpp">C++</option>
<option value="py">Python</option>
<option value="php">PHP</option>
<option value="java">JAVA</option>
<!--option value="vbs">VBSCRIPT</option-->
</select>
        <select id=SWORKS onchange="getFile(this.value,1);" style="visibility: hidden;position: relative;top: -340px;left: 32px;width:158px" title="如果之前提交过某一语言的源码，则可以直接打开。"><option value="">请选择源码文件</option></select>
      </div>
    </div>

    <!--左侧-->
    <div style="float:left;width: 666px;height:705px;margin-right: 6px;margin-bottom: 10px;background:beige ;/* margin-left: 10px; */">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <!--预设任务分发-->
        <div style="text-align: left;">问题：</div>
        <div id=BDBTN style="position: absolute;top: -2px;right: -1px;cursor: pointer;background: rgb(240, 240, 240);border: 1px solid;width: 30px;height: 18px;background:rgb(220, 87, 19)" contenteditable="false" onclick="showBDBTN();" title="点击打开/关闭小白板"></div>

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
        <div style="position: relative;top: 170px;left:5px">
          <div style="text-align: left;">操作说明：<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1.依次选择班级、任务和编程语言种类后，如果之前提交过代码，则会自动调取并显示代码；<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2.代码编写完成后，在运行前，可以根据需要设置“执行参数”和“输入数据”；<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.可以根据“编译结果”排查代码中的错误；<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4.如果代码正确，且程序有结果输出，则会显示在“输出结果”中；<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5.每个程序最多执行3秒钟，如算法过于复杂而超时，将不能得到正确的结果。<p>
            <font color=red>注意：<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;请勿编写和运行会破坏系统的代码，请遵守《中华人民共和国计算机信息系统安全保护条例》。</font></div>
        </div>
      </div>
    </div>
    <!--左侧-->

    <!--右侧-->
    <div alignstyle="0" style="float:left;width: 666px;height: 705px;position:relative;background: beige;margin-bottom: 10px;">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <div style="text-align: left;">源代码：</div>

        <div id=FSIZE1 style="position: absolute;top: -2px;right: -1px;cursor: pointer;background: rgb(255,255,255);border: 1px solid;width: 20px;height: 18px;font-weight: bold;" contenteditable="false" onclick="setSize(-2);" title="缩小字体">-</div>
        <div id=FSIZE2 style="position: absolute;top: -2px;right: 20px;cursor: pointer;background: rgb(255,255,255);border: 1px solid;width: 20px;height: 18px;font-weight: bold;" contenteditable="false" onclick="setSize(2);" title="放大字体">+</div>

        <textarea id="text-code" onchanged="autoSave();"></textarea>
        <div id="text-code-ace" class="hidden"></div>
        <div style="width: 310px;top: 432px;/* left: 50px; */position:absolute;">
          <div style="text-align: left;">执行参数：</div>
          <input type=text id="inputargs">
          <div style="text-align: left;">输入数据：</div>
          <textarea  id="inputdata"></textarea>
          <input type=button id="launch-save" value="保存&测试" onclick="checkCode();" disabled style="position: relative;top: 5px;width:150px;height: 30px;font-size: 14px;font-weight: bolder;">
          <input type=button id="dl_button" value="下载程序" onclick="downloadExec();" style="position: relative;top: 5px;width:150px;height: 30px;font-size: 14px;font-weight: bolder;" disabled>
          <input type=button id="launch-evaluate" value="判分" onclick="evaluateCode();" disabled style="position: relative;top: 14px;width: 305px;height: 35px;font-size: 18px;font-weight: bolder;">
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
  <div style="float:left;width: 100%;max-width: 1338px; /*  margin-top:10px;margin-right: 650px; */ margin-bottom:10px; min-width: 666px;">
     <div style="background:darkgray;text-align:center">Copyright</div>
  </div>
</div>
</div>
</div>
<!--MAIN-->
<iframe name=dl id=ld height=0 width=0 style="display:none"></iframe>
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
    document.getElementById("launch-save").addEventListener("click", function () {
        editorElement.innerHTML = editor.getValue();
    });
</script>

<script>
function setSize(n)
{
   if(document.getElementById("text-code-ace").style.fontSize=='') 
      document.getElementById("text-code-ace").style.fontSize="14px";
   else
      document.getElementById("text-code-ace").style.fontSize=parseInt(document.getElementById("text-code-ace").style.fontSize)+n+".px";

   if(parseInt(document.getElementById("text-code-ace").style.fontSize)<12)
   {
      document.getElementById("text-code-ace").style.fontSize="12px";
   }
}

//响应回车
document.addEventListener('keyup',function (event){
   //if(event.keyCode == 13){
      if(document.getElementById("INPUT_TKID").value)
         getTasks(document.getElementById("INPUT_TKID").value);
   //}
});

var bChanged=false;
function autoSave()
{
   bChanged=true;
}


function downloadExec()
{
   window.frames['dl'].location.href="./opt/dl.php?t="+ext+'&CN='+classname+'&TKID='+taskID;

}

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
   else if( data=="" && document.getElementById("demoinput").value!="")
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
            if(dataArr[2]=='')
            {
               document.getElementById("dl_button").removeAttribute("disabled");
               //document.getElementById("launch-save").removeAttribute("disabled");
               //document.getElementById("launch-evaluate").removeAttribute("disabled");
            }
            else
            {
               document.getElementById("dl_button").setAttribute("disabled",true);
               //document.getElementById("launch-save").setAttribute("disabled",true);
               //document.getElementById("launch-evaluate").setAttribute("disabled",true);
            }
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
      gradeid=GID=taskID;
      document.getElementById("EXT").style.visibility="visible";
      getTask(taskID);
      if(ext)
         getFile(ext);

      loadBDAttach(taskID);
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

      var o=document.getElementsByClassName("ace_text-input");
      o[0].disabled=true;

      if(taskID)
      {
         o[0].disabled=false;
         document.getElementById("INPUT_TKID").value=taskID;
      }
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
               {
                  document.getElementById("launch-save").removeAttribute("disabled");
                  document.getElementById("launch-evaluate").removeAttribute("disabled");
                  //editor.setValue(data);
                  typeWriter(data, "textElement", 100);
               }
            }
         });

      }
      else
      {
         $.post("./opt/getCode.php?T="+value, {"UN":username, "CN":classname, "TKID":taskID, "L":ext}, function (data) {
            //var jsonData=JSON.parse(data);
            if(data)
            {

               var oLIST=document.getElementById("SWORKS");
               oLIST.style.visibility="visible";
               oLIST.length=0;
               oLIST.append(new Option("请选择源码文件",""));
               for(var i=0;i<data.length;i++)
               {
                  oLIST.append(new Option(data[i],data[i]));
               }
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
            {
               document.getElementById("launch-save").removeAttribute("disabled");
               document.getElementById("launch-evaluate").removeAttribute("disabled");
               //editor.setValue(data);
               typeWriter(data, "textElement", 100);
            }
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


<!--小白板-->

   <!--小白板-->
   <div alignstyle="0"  ID="WHITEBOARD" style="position: absolute;border-radius: 3%;z-index:9999; background: #DC5713;width: 656px;height: 518px; margin-bottom: 10px;font-size: 13px; left: 50%; top: 50%; -webkit-transform: translate（-50%，-50%）;transform: translate(-50%,-50%);display:block">
     <div style="position:relative;width:100%;top:0px;left:29px;"  >
<?php
   //获取白板现有数据
   $pos=@file_get_contents("./board/".$room."/".$classid.".bdpos.dat");//获取已发布的数据

   $posArr=json_decode($pos);		//已被添加的数据：类型(IMG/DIV)，图片文件路径/DIV数据，left坐标，Top坐标
   //print_r($posArr);

   $idArr=Array();			//已被添加的数据中的ID：文件名
   $existedDIV="";			//已被添加的数据生成的DIV
   if($posArr!=NULL)  			//每组数据由四个数据组成：IMG/DIV, IMG URL/DIV TEXT, LEFT, TOP
   {

      for($i=0;$i<count($posArr);$i++)
      {
         $idtype=$posArr[$i][0];               //类型
         if($idtype=="IMG")
         {
            $ids=explode("/",$posArr[$i][1]);   //IMG中放图片URL，DIV中放文本

            $idsc=count($ids);
            $id=substr($ids[$idsc-1],0,strrpos($ids[$idsc-1],'.'));

            $bLine=($ids[$idsc-2]=="LINES")?true:false;

            $idArr[]=$ids[$idsc-1];
            if($admin)			//教师可以对DIV进行管理：拖动和删除
            {
               $existedDIV.="           <div id='".$id."' class='ATTACH' style='position: absolute;left:".$posArr[$i][2]."px;top:".$posArr[$i][3]."px'  onmouseover='showControl(this);' onmouseout='hideControl(this);'>\r\n";
               $existedDIV.="             <div style='visibility:hidden;background: ghostwhite; width: 62px; height: 22px; position: relative;'>\r\n";
               $existedDIV.="               <img title='回收' src='./img/recycle.png' style='position: relative;display: inline-block;top: 2px;' onclick='recycleImg(\"".($bLine?"./LINES/":"").$ids[$idsc-1]."\");'>\r\n";

               if(stripos($ids[$idsc-1],".gif")==false)
                  $existedDIV.="               <img title='编辑' src='./img/edit.png' style='position: relative;display: inline-block;left:1px; top: 2px;' onclick='editImg(\"".($bLine?"./LINES/":"").$ids[$idsc-1]."\");'>\r\n";

               $existedDIV.="               <img title='彻底删除' src='./img/delete.png' style='position: relative;display: inline-block;left:2px; top: 2px;' onclick='deleteImg(\"".($bLine?"./LINES/":"").$ids[$idsc-1]."\");'>\r\n";
               $existedDIV.="             </div>\r\n";
               $existedDIV.="             <img src='".$posArr[$i][1]."' title='".$id."' onmousedown='dragBD(this,event);'>\r\n";
               $existedDIV.="           </div>\r\n";
            }
            else				//学生只能查看。
            {
               $existedDIV.="           <div id='".$id."' class='ATTACH' style='position: absolute;left:".($posArr[$i][2]+12)."px;top:".($posArr[$i][3]+16)."px'>";		//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               $existedDIV.="             <img src='".$posArr[$i][1]."' title='".$id."' alt='".$id."' onmousedown='dragBD(this,event,3);'>";
               $existedDIV.="           </div>";
            }
         }
         else if($idtype=="VIDEO")
         {
            $ids=explode("/",$posArr[$i][1]);   //IMG中放图片URL，DIV中放文本
            $idsc=count($ids);
            $id=substr($ids[$idsc-1],0,strrpos($ids[$idsc-1],'.'));
            $idArr[]=$ids[$idsc-1];
            if($admin)			//教师可以对DIV进行管理：拖动和删除
            {
               $existedDIV.="           <div id='".$id."' class='ATTACH' style='position: absolute;left:".$posArr[$i][2]."px;top:".$posArr[$i][3]."px'  onmouseover='showControl(this);' onmouseout='hideControl(this);'>\r\n";
               $existedDIV.="             <div style='visibility:hidden;background: ghostwhite; width: 90px; height: 22px; position: relative;'>\r\n";
               $existedDIV.="               <img title='回收' src='./img/recycle.png' style='position: relative;display: inline-block;top: 2px;' onclick='recycleImg(\"".$ids[$idsc-1]."\");'>\r\n";

               $existedDIV.="             <img title='增加高度' src='./img/vup.png' style='position: relative;display: inline-block; left:4px;top: 2px;' onclick='videoHeightUP(this);'>";
               $existedDIV.="             <img title='降低高度' src='./img/vsub.png' style='position: relative; display: inline-block;left:8px;top: 2px;' onclick='videoHeightDOWN(this);'>";

               $existedDIV.="               <img title='彻底删除' src='./img/delete.png' style='position: relative;display: inline-block;left:12px; top: 2px;' onclick='deleteImg(\"".$ids[$idsc-1]."\");'>\r\n";
               $existedDIV.="             </div>\r\n";
               $existedDIV.="             <video controls height='".$posArr[$i][4]."' src='".$posArr[$i][1]."' title='".$id."' onmousedown='dragBD(this,event);'>\r\n";
               $existedDIV.="           </div>\r\n";
            }
            else				//学生只能查看。
            {
               $existedDIV.="           <div id='".$id."' class='ATTACH' style='position: absolute;left:".($posArr[$i][2]+12)."px;top:".($posArr[$i][3]+16)."px'>";		//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               $existedDIV.="             <video height='".$posArr[$i][4]."' src='".$posArr[$i][1]."' title='".$id."'  onmousedown='drag(this,event,3);'>";
               $existedDIV.="           </div>";
            }
         }
         else if($idtype=="DIV")
         {
            if($admin)			//教师可以对DIV进行管理：拖动和删除
            {
               $existedDIV.="           <div id='NEWTEXT_".($i+1)."' class='ATTACH' style='position: absolute;width:60px; left:".$posArr[$i][2]."px;top:".$posArr[$i][3]."px' onmouseover='showControl(this);' onmouseout='hideControl(this);'>";
               $existedDIV.="           <div style='visibility:hidden;background: ghostwhite;width:146px;'>";
               $existedDIV.="             <img title='回收' src='./img/recycle.png' style='position: relative;display: inline-block;top: 2px;' onclick='removeText(\"NEWTEXT_".($i+1)."\");'>";
               $existedDIV.="             <img title='加大字号' src='./img/fadd.png' style='position: relative;display: inline-block;left:0px;top: 2px;' onclick='sizeUP(this);'>";
               $existedDIV.="             <img title='缩小字号' src='./img/fsub.png' style='position: relative;display: inline-block;left:2px;top: 2px;' onclick='sizeDOWN(this);'>";
               $existedDIV.="             <img title='收窄' src='./img/wsub.png' style='position: relative;display: inline-block;left:2px;top: 2px;' onclick='widthDOWN(this);'>";
               $existedDIV.="             <img title='拉宽' src='./img/wadd.png' style='position: relative;display: inline-block;left:2px;top: 2px;' onclick='widthUP(this);'>";

               preg_match("/color: ([^^]*?);/",$posArr[$i][1],$m);
               $select_color='';
               if(count($m)==2 && $m[1]!="")
               $select_color=$m[1];

               $existedDIV.="             <select title='修改颜色' style='position: relative;display: inline-block;left: 2px;top:-2px;color:".$select_color."' onchange='changeColor(this);'>";
               $existedDIV.="	            <option style='color:black;' ".($select_color=="black"?"selected":"")." value=black>黑</option>";
               $existedDIV.="	            <option style='color:white;' ".($select_color=="white"?"selected":"")." value=white>白</option>";
               $existedDIV.="	            <option style='color:red;' ".($select_color=="red"?"selected":"")." value=red>红</option>";
               $existedDIV.="	            <option style='color:yellow;' ".($select_color=="yellow"?"selected":"")." value=yellow>黄</option>";
               $existedDIV.="	            <option style='color:blue;' ".($select_color=="blue"?"selected":"")." value=blue>蓝</option>";
               $existedDIV.="	            <option style='color:green;' ".($select_color=="green"?"selected":"")." value=green>绿</option>";
               $existedDIV.="             </select>";
               $existedDIV.="           </div>";

               $existedDIV.="         <div onmousedown='dragBD(this,event);' ondblclick='editText(this);'>".$posArr[$i][1]."</div></div>";
            }
            else				//学生只能查看。
            {
               $existedDIV.="         <div id='NEWTEXT_".($i+1)."' class='ATTACH' style='position: absolute;left:".($posArr[$i][2]+12)."px;top:".($posArr[$i][3]+17)."px'>";	//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               $existedDIV.="           ".$posArr[$i][1]."</div>";
            }
         }
         else if($idtype=="COVER")
         {

echo $admin;
echo "COVER";
            if($admin)
               $existedDIV.="         <div id='NEWTEXT_".($i+1)."' class='ATTACH' style='width: 650px;    height: 480px;    position: absolute;    left: -16px;    top: 25px;    background: beige;border-radius: 3%;'    ondblclick=\"this.style.visibility='hidden';\"></div>";	//遮罩页
            else
               $existedDIV.="         <div id='NEWTEXT_".($i+1)."' class='ATTACH' style='width: 650px;    height: 480px;    position: absolute;    left: -6px;    top: 19px;    background: beige;border-radius: 3%;' onmousedown='drag(this,event,1);'></div>";	//遮罩页
         }
      }
   }
?>
       <div class="circle" <?php echo ($admin)?"onmouseover=\"hideOPTMenu();\" onmouseup='hideHistoryTXT();'":"";?>  id="WB" alignstyle="0" style="border-radius: 3%;background: white;width: 650px;height:480px;left: -26px;top:35px;position:absolute;"></div>
<?php
   //遍历./board文件夹，获取小白板已上传图片附件
   $bd_files=Array();				//白板中已存在的图片资源
/*
   $add="./board/".$room."/".$taskID."/";
   if(is_dir($add))
   {
      if ($handle_date = opendir($add))
      {
         while (false !== ($file = readdir($handle_date)))
         {
            if (!is_dir($add.$file))
            {
               if(!in_array($file,$idArr))		//过滤掉已经使用的附件。
               {
                  $bd_files[]=$file;
               }
            }
         }
         closedir($handle_date); 
      }
   }
*/
   $bfc=count($bd_files);			//白板中已存在图片计数器
?>
       <div id="header"   style="left:-12px;top:6px;position:absolute; width:620px;" ><!--白板表头-->
       <div id="drag"  ondragstart='dragBD(this,event);' onmousedown='drag(this,event,2);' style="left: -17px; top: -6px; position: absolute; width: 656px; height: 518px;" <?php echo ($admin)?"onmouseover=\"hideOPTMenu();\" onmouseup='hideHistoryTXT();'":"";?>></div>
<?php
   if($admin)//教师显示控制按钮
   {
      echo "         <select id=attach onchange='addImg();' title='添加预设的图片板书' style='height: 25px;width:90px;position: absolute;left:-6px;'><option value=''>请选择素材</option>";
      for($i=0;$i<$bfc;$i++)
      {
         echo "<option value=".$bd_files[$i].">".substr($bd_files[$i],0,strrpos($bd_files[$i],'.'))."</option>";
      }
      echo "</select>";
?>
         <div style="position: absolute;left: 83px;top: 1px; font-size: 18px; height:25px;color:white;">|</div>
         <div style="position: absolute;top: 0px;left: 86px;z-Index:99999;">
           <input type="text" style="width:155px; height:19px; " id="addtxt" placeholder="此处可粘贴图片/文字/视频" autocomplete="off"   onfocus="showHistoryTXT();" oninput="showHistoryTXT();">
           <div id=historyTXT style="visibility:hidden;"></div>
           <select id="fontsize" style="position: absolute;top: 0px;left: 162px;height: 25px;" title="设置文字的字号/线条的粗细">
             <option style="font-size:14px;" value="14" title="线条粗细为1">14</option>
             <option style="font-size:18px;" value="18" title="线条粗细为2">18</option>
             <option style="font-size:22px;" selected value="22" title="线条粗细为3">22</option>
             <option style="font-size:26px;" value="26" title="线条粗细为4">26</option>
             <option style="font-size:30px;" value="30" title="线条粗细为5">30</option>
             <option style="font-size:34px;" value="34" title="线条粗细为6">34</option>
           </select>
           <select id="color" style="position: absolute;top: 0px;left: 198px;height: 25px;" title="设置文字的颜色" onchange="this.style.color=this.value;">
             <option style="color:black;" value="black">黑</option>
             <option style="color:white;" value="white">白</option>
             <option style="color:red;" value="red">红</option>
             <option style="color:yellow;" value="yellow">黄</option>
             <option style="color:blue;" value="blue">蓝</option>
             <option style="color:green;" value="green">绿</option>
           </select>
           <input style="position: absolute;top: 0px;left: 235px;height: 25px;" type="button" value="添加" title="在小白板中添加文字板书。" onclick="addText();">

           <input style="position: absolute;top: -3px;left: 276px;height: 25px;" type="checkbox" title="选中后可添加鼠标手绘线条" onclick="addLines();"  id="DRAWLINES">
           <a onclick="undoOneStep();" style="position: absolute; top: 2px; left: 294px; height: 25px; width: 40px; font-size: 14px;color:white;cursor:pointer" title="删除小白板上最后一个数据">撤销</a>


           <div style="position: absolute;left: 324px;top: 1px; height:25px; font-size: 18px;color:white;">|</div>
           <div style="position: absolute;left: 328px;top: 1px; height:25px;">
             <input type=button style='position: absolute;left: 0px;background:white;height:22px;border:white' onclick="setBGColor('white');" title='设置小白板的背景色'>
             <input type=button style='position: absolute;left: 13px;background:black;height:22px;border:black;' onclick="setBGColor('black');" title='设置小白板的背景色'> 
             <input type=button style='position: absolute;left: 26px;background:bisque;height:22px;border:bisque;' onclick="setBGColor('bisque');" title='设置小白板的背景色'>
             <input type=button style='position: absolute;left: 39px;background:burlywood;height:22px;border:burlywood;' onclick="setBGColor('burlywood');" title='设置小白板的背景色'>
             <input type=button style='position: absolute;left: 52px;background:chocolate;height:22px;border:chocolate;' onclick="setBGColor('chocolate');" title='设置小白板的背景色'>
             <input type=button style='position: absolute;left: 65px;background:darkgoldenrod;height:22px;border:darkgoldenrod;' onclick="setBGColor('darkgoldenrod');" title='设置小白板的背景色'>
             <input type=button style='position: absolute;left: 78px;background:darkseagreen;height:22px;border:darkseagreen;' onclick="setBGColor('darkseagreen');" title='设置小白板的背景色'>
             <!--input type=button style='position: absolute;left: 91px;background:darkgray;height:22px;border:darkgray;' onclick="setBGColor('darkgray');" title='设置小白板的背景色'-->
           </div>
           <div style="position: absolute;left: 431px;top: 1px; height:25px; font-size: 18px;color:white;">|</div>
           <div style="position: absolute;top: 0px;left: 430px;width:68px;height:25px;z-Index:99999" onmouseover="showOPTMenu();" >
             <input  type="button" value="更多操作" style="height: 25px;">
             <div id=menu style="visibility:hidden;">
               <input style="position: relative;width:70px;" type="button" value="擦黑板" title="清空小白板上所有内容。" onclick="cleanBD();">
               <input style="position: relative;width:70px;" type="button" value="空白页" title="插入空白页，盖住现有内容。" onclick="coverBD();">
               <input style="position: relative;width:70px;" type="button" value="小白板" title="强制学生端打开小白板。" onclick='add("[\"MENU\",\"\"]");'>

               <!--input style="position: relative;width:70px;" type="button" value="学生作品" title="查看当前学生已上交作品" onclick="showWorks();"-->
               <!--input style="position: relative;width:70px;" type="button" value="在线学生" title="查看当前学生端登录情况" onclick="showList();"-->
               <!--input style="position: relative;width:70px;" type="button" value="下载作品" title="批量下载Scratch比赛作品" onclick="getWorks();"-->
               <!--input style="position: relative;width:70px;" type="button" value="刷新" title="强制刷新学生端，包括上课登记页面。" onclick="refreshRemote();"-->
               <!--input style="position: relative;width:70px;" type="button" value="置顶" title="强制学生端的页面回到顶部。" onclick="goTop();"-->
               <!--input style="position: relative;width:70px;" type="button" value="清除" title="清除学生端所有Cookie数据。" onclick="doCleaning();"-->

               <input style="position: relative;width:70px;" type="button" value="重置服务" title="为保证稳定，预防性地重启WebSocket服务器。" onclick="reboot1();">

               <input style="position: relative;width:70px;" type="button" value="存档" title="保存当前小白板数据。" onclick="saveBD(1);">
               <input style="position: relative;width:70px;" type="button" value="调档" title="调取小白板历史数据。" onmouseover="getBOARDList();">
               <div id=sub></div><!--小白板历史存档数据列表-->
               <!--input style="position: relative;width:70px;" type="button" value="任务单" title="强制学生端打开任务单。" onclick="showTask();"--> 
             </div>
           </div>
           <div style="position: absolute;left: 500px;top: 0px; height:25px; font-size: 18px;color:white;">
             <input type=checkbox id=DRAG checked=true onclick="setDragFunc();" title="白板内容拖曳控制">
           </div>
         </div>


        <div id="msg_argviewer"  onmousedown="drag(this,event,1);" style="width: 620px;border: 1px solid gray;height: 383px;float: left;text-align: left;left: 0px;position: absolute;top: 110px;display:none;z-index:9999;overflow: hidden;border-radius:3%; background: white;">
          <div style="position: relative; height: 20px; border-bottom: 1px solid green; background: lightgrey; font-size: 16px; font-weight: bold;" onclick="setViewerScroll(this);">
            <span style="left: 50px; position: absolute;">姓名</span>
            <span style="left: 120px; position: absolute; text-align: center; width: 80px;">用时</span>
            <span style="left: 210px; position: absolute; text-align: center; width: 80px;">步长</span>
            <span style="left: 300px; position: absolute; text-align: center; width: 80px;">左转</span>
            <span style="left: 390px; position: absolute; text-align: center; width: 80px;">右转</span>
          </div>
          <div style="position: relative; height: 20px; font-size: 16px; font-weight: bold;top:-21px;left:500px">
            <input type="button" value="清零" style="left: 0px; top: -1px; position: absolute; text-align: center; width: 61px; height: 22px; font-size: 14px;" onclick="argArr.length=0;document.getElementById('msg_argviewer_data').innerHTML='';">
            <input type="button" value="暂停" style="left: 60px; top: -1px; position: absolute; text-align: center; width: 61px; height: 22px; font-size: 14px;" onclick="setDataCollectingStatus(this);">
          </div>
          <div id=msg_argviewer_data style="top: -20px; position: relative;"></div>
        </div>


        <div id="msg_argviewer2"  onmousedown="drag(this,event,1);" style="width: 620px;border: 1px solid gray;height: 383px;float: left;text-align: left;left: 0px;position: absolute;top: 110px;display:none;z-index:9999;overflow: hidden;border-radius:3%; background: white;">
          <div style="position: relative; height: 20px; border-bottom: 1px solid green; background: lightgrey; font-size: 16px; font-weight: bold;" onclick="setViewerScroll(this);">
            <span style="left: 25px; position: absolute;">玩家</span>
            <span style="left: 90px; position: absolute; text-align: center; width: 200px;">作品</span>
            <span style="left: 280px; position: absolute; text-align: center; width: 80px;">得分</span>
            <span style="left: 400px; position: absolute; text-align: center; width: 80px;">时间</span>
          </div>
          <div style="position: relative; height: 20px; font-size: 16px; font-weight: bold;top:-21px;left:500px">
            <input type="button" value="清零" style="left: 0px; top: -1px; position: absolute; text-align: center; width: 61px; height: 22px; font-size: 14px;" onclick="argArr2.length=0;document.getElementById('msg_argviewer2_data').innerHTML='';">
            <input type="button" value="暂停" style="left: 60px; top: -1px; position: absolute; text-align: center; width: 61px; height: 22px; font-size: 14px;" onclick="setDataCollectingStatus(this);">
          </div>
          <div id=msg_argviewer2_data style="top: -20px; position: relative;"></div>
        </div>


         <img id="closeWB" style="position: absolute;visibility:hidden;height:24px;width:24px;right:-10px;top:0px;" src="./img/close.png" title="隐藏小白板" alt="隐藏小白板" onclick="showBDBTN();">
       </div>
<?php
   }
   else echo "       <div  style='width: 615px;text-align: center;font-size:18px;color: white;'>小&nbsp;&nbsp;白&nbsp;&nbsp;板</div> <img  id='refreshWB' style='position: absolute;visibility:hidden;height:24px;width:24px;left:0px;top:1px;' src='./img/refresh.png' title='刷新小白板（新增回放功能）' alt='刷新小白板（新增回放功能）' onclick='playBDPos(null);'><img  id='closeWB' style='position: absolute;visibility:hidden;height:24px;width:24px;right:-10px;top:1px;' src='./img/close.png' title='隐藏小白板' alt='隐藏小白板' onclick='showBDBTN();'>";	//学生端显示小白板
   echo "       <div id='attachments' style='left:-10px;top:10px;position:absolute;'>";					//存放白板上的图片和文字
   echo $existedDIV;														//已存在的图片或文本DIV
   echo "</div>";
   echo "        <input type=txt name=texteditor id=texteditor style=\"visibility:hidden;position: absolute;left:0px;top:0px;width:0px;height:0px;z-index:9;\">";
?>  
<script>

function hideBackground(o)
{
   if(o.checked==true)
   {
      document.getElementById("waiting").style.visibility="visible";
   }
   else
   {
      document.getElementById("waiting").style.visibility="hidden";
   }
}


function setDataCollectingStatus(o)
{
   if(o.value=='暂停') {
      dataCollecting=false;
      o.value='继续';
      var msg = {'content': '["DATACOLLECTINGEND"]', 'type': 'user'};//定向发送给老师
      sendMsg(msg);
   }
   else {
      dataCollecting=true;
      o.value='暂停';
      var msg = {'content': '["DATACOLLECTINGBEGIN"]', 'type': 'user'};//定向发送给老师
      sendMsg(msg);
   }
}

function setViewerScroll(o)
{
   if(o.parentElement.style.overflow=="hidden")
   {
      o.parentElement.style.overflow="auto";
      o.parentElement.style.borderRadius="0%";
   }
   else
   {
      o.parentElement.style.overflow="hidden";
      o.parentElement.style.borderRadius="3%";
   }
}

$(document).ready(function(){
   $("input").keydown(function (event){
      if(event.keyCode == 13){
        saveEditorText();
      }
   });
});


</script>  
<div id=back style="position: absolute;  left: <?php echo $admin?262:274;?>px;   top: <?php echo $admin?491:485;?>px;visibility:hidden"><img src=./img/back.png title="上一条" onclick="PlayBack();"></div>
<div id=all style="position: absolute;   left: <?php echo $admin?287:299;?>px;    top: <?php echo $admin?491:485;?>px;visibility:hidden"><img src=./img/all.png title="最后一条" onclick="PlayAll();"></div>
<div id=pause style="position: absolute; left: <?php echo $admin?312:324;?>px;  top: <?php echo $admin?491:485;?>px;visibility:hidden"><img src=./img/play.png title="下一条" onclick="continuePlay();"></div>
<?php
   if($admin)		//教师端底部背景色控制
   {
?>
       <canvas id="myLines" width="650px" height="480px" style="visibility:hidden;position: absolute; width: 650px; height: 480px; left: -26px; top: 35px; border-radius: 3%; z-index: 9999999;">></canvas>

       <div id=tail style="position: absolute;top: 516px;left: -12px;">
         
<?php
$afc=0;
      if($afc>0)			//附件显示以及相关链接的控制
      {
         echo "         <label style='position: absolute;left: 125px;width: 100px'><select  onchange=\"showAttach(this);\" style=\"width:90px;\"><option>请选择附件</option>";
         echo "<option value=\"|HIDEALLNOW|\">隐藏全部附件</option>";
         for($a=0;$a<$afc;$a++)
         {
            echo "<option value=".$att_files[$a].">".$att_files[$a]."</option>";
         }
         echo "         </select></label>";
      }
?>       </div>
<?php
   }
?>
        </div>
      </div>
    </div>
<script src=./js/page_bd.js></script>
<script>
//手动刷新小白板
function refreshWB()
{
   //updateBOARD();
   //bPLAYALL=true;
   playBDPos(null);
}


refreshWB();
//隐藏小白板
function hideWB()
{
   document.getElementById("WHITEBOARD").style.visibility="hidden";
   document.getElementById("closeWB").style.visibility="hidden";
<?php
if(!$admin){
?>
   document.getElementById("refreshWB").style.visibility="hidden";
<?php
}
?>
   hideOPTMenu();
}
</script>

<?php
   if($admin)
   {
?>
<!--绘画作品展示-->
<script>
   var nLast=-1;			//上一次显示的图片ID
   var bType=false;		//默认单图模式，true为多图模式
   var nSpeed=5000;		//默认5秒刷新一次

   //调整切换更新
   function changeSpeed(o)
   {
      nSpeed=o.value;
      window.frames["viewer"].contentWindow.changeSpeed();
   }

   //切换视图
   function setView()
   {
      window.frames["viewer"].contentWindow.setView(bType);
      document.getElementById("SETVIEW").src=bType?"./img/align_m.png":"./img/align_s.png";
      document.getElementById("SETVIEW").title=bType?"切换到多图模式":"切换到单图模式";
      bType=!bType;
   }

   //关闭窗口
   function closeIV()
   {
      document.getElementById("ImageViewer").style.visibility="hidden";	
      document.getElementById("viewer").src="about:blank";
   }

   //刷新数据
   function reloadIV()
   {
      window.frames["viewer"].contentWindow.getWorks();
   }

   function pause(o)
   {
      window.frames["viewer"].contentWindow.pause();
      o.title=o.title=="暂停"?"继续":"暂停";
      o.src=o.title=="暂停"?"./img/pause.png":"./img/play.png";
      
   }

</script>
<?php
   }
?>




<?php
   if($admin)//教师端图片编辑器
   {
?>
<!--图片编辑器-->
 <div id="ImageEditor"  contenteditable="true" style="border: 1px solid;min-height:80px;min-width:444px;cursor: context-menu;position: absolute;left:180px;top: 180px;width: 300px;height: 444px;z-index: 99990;background-color: rgb(204, 204, 204);visibility:hidden; ">
      <div id="CP">
        <div onmousedown="drag(this,event,0)" id=COLOR style="background-color:#0a0a0a">
          <div id=TITLE style="position:absolute;left:0px;top:5px;color:white;" align="center">
            &nbsp;&nbsp;图片编辑
            <div style="left: 85px;/* width: 110px; */position: relative;height: 27px;width: 70px;/* right: 5px; */top: -24px;">
              <img id="IEUNDO" title="撤销" onclick="ieundo();" src="./img/undo.png" style="position: absolute; left: 0px; visibility: hidden; top: 7px;"><SPAN id=IEUNDOV  style="position: absolute;left: 0px;font-size: 12px; top: -3px; text-align: center;"></SPAN>
              <img id="IEREDO" title="重做" onclick="ieredo();" src="./img/redo.png" style="position: absolute; right: 0px; visibility: hidden; top: 7px;"><SPAN id=IEREDOV style="position: absolute;right: 0px;font-size: 12px;top: -3px;text-align: center;"></SPAN>
            </div>
          </div>
          <div align="right" ><img style="position: relative;height:24px;width:24px;right: 7px; top: 7px;" src="./img/close.png" onclick="closeIE();"><hr></div>
        </div>
        <div style="margin: 0px 7px auto;text-align: center;">
          <div align="center" id=COLOR_CHANGER style="visibility:visible">
            <!--原图放在这里，原图不显示-->
            <div align="center" id=BACKUP style="display:none"><img id=RAW style="display:none"></div><!--预览图放在这里-->
            <div align="center" id=PREVIEW style="cursor: initial;"></div><!--预览图放在这里-->
            <div align="center" id=SHIELD style="height:0px;"></div><!--遮罩图放在这里-->
          </div>
          <div align="center" id=RECT_SELECTOR style="height:0px;visibility:hidden"><!--切图选择器放在这里-->
            <canvas align="center" id=SELECTOR style="width:0px;height:0px"></canvas>
          </div>

          <div align="center">
            <hr>
            <label><input type=checkbox name=IES id=IES onclick="setColorPicker();" title="鼠标在图片上点选颜色，即可完成相应的透明操作。">修改颜色<span ID=INFO style="font-size:12px;">(当前为切图模式)</span></label>
            <div id="SETTRANS" style="display: block;/* left: 20px; */position: relative;width: 100%;height: 158px;text-align: left;">
              <hr>
              <div style="width: 114px;position:absolute;text-align: left;top:0px;">
                <span style="position: absolute;left: 8px;top: 8px;">操作：</span>
                <label style="position: relative;top: 33px;width: 110px;left: 6px;"><input type="radio" name="MTYPE" value="CM" checked  onclick="setAlgorithm(this);" style="width:18px;">简单替换</label><br>
                <label style="position: relative;top: 35px;width: 110px;left: 6px;"><input type="radio" name="MTYPE" value="FF" onclick="setAlgorithm(this);" style="width:18px;">区域填充</label>
                <hr style="position: relative;    top: 32px;    width: 81px;    left: -3px;">
                <label style="position: relative;top: 27px;width: 110px;left: 6px;"><input type="checkbox" id="NEWOLD" onclick="addColor();" style="width:18px;">追加模式</label><br>
              </div>

              <hr style="position: absolute; top: 115px; width: 400px; left: 12px;">
              <label style="position: relative;top: 124px;left:14px;">
                 <span style="position:absolute;font-size:14px;right:4px;top:-7px;text-align:center;" id="GVALUE">0</span>
                 颜色匹配阈值：<input type="range" id="GATE" style="width: 290px;top:5px;position: relative;" max="255" min="0" value="0" step="1" onchange="setPreview(this);" title="阈值控制">
              </label>

              <div style="height:40px;width:305px;position:absolute;left: 124px;top: 8px;text-align:left;">
                <span style="position: absolute;text-align: left;left: -4px;">设置替换颜色：</span>
                <input type="button" id="COLORVIEW" style="position: relative;left: 6px;top: 29px;width: 64px;height: 54px;background: rgb(0, 0, 0, 0);">
                <label style="position: absolute;left:2px;top: 86px;width: 200px;"><input type="checkbox" checked="" name="T" id="T" onclick="setColorType();" title="鼠标在图片上点选颜色，即可完成相应的透明操作。">透明色</label>
                <div id="RGBA" style="position: absolute;left: 76px;text-align: left;top: 24px;">
                   R:<input type="text" value="0" id="RV" style="width: 26px;text-align: center;left: 20px;position: absolute;" onchange="setSubValue(this);"><input type="range" style="width: 160px;left: 58px;position: absolute;" id="RR" max="255" min="0" value="0" step="1" onchange="setSubColor(this);"><br>
                   G:<input type="text" value="0" id="GV" style="width: 26px;text-align: center;left: 20px;position: absolute;" onchange="setSubValue(this);"><input type="range" style="width: 160px;left: 58px;position: absolute;" id="GR" max="255" min="0" value="0" step="1" onchange="setSubColor(this);"><br>
                   B:<input type="text" value="0" id="BV" style="width: 26px;text-align: center;left: 20px;position: absolute;" onchange="setSubValue(this);"><input type="range" style="width: 160px;left: 58px;position: absolute;" id="BR" max="255" min="0" value="0" step="1" onchange="setSubColor(this);"><br>
                   A:<input type="text" value="0" id="AV" style="width: 26px;text-align: center;left: 20px;position: absolute;" onchange="setSubValue(this);"><input type="range" style="width: 160px;left: 58Px;position: absolute;" id="AR" max="255" min="0" value="0" step="1" onchange="setSubColor(this);"><br>
                </div>
              </div>
            </div>

            <div style="position: relative;top:0px;"><hr><input type="button" id="SAVE" onclick="uploadImg();" title="上传图片" value="上传">&nbsp;<input type="button" value="切除周边透明区域后上传" title="去掉四周透明" onclick="shrinkImg();"></div>
          </div>
        </div>
      </div>
    </div>
<!--图片编辑-->

<!--小白板-->
<!--小白板-->
  </div>
<?php
}
?>
<!-- Teaching Platform Websocket Scripts -->
<script src="./js/websocket.js"></script>
<script type="text/javascript">
   var uname = "<?php echo $username;?>";
   var strWebSocketServerURL="ws://<?php echo $WSSERVER;?>:<?php echo (8080+$room);?>";		//上课管理
   createWebSocket(strWebSocketServerURL);
   document.getElementById("closeWB").style.visibility  ="visible";				//小白板窗口关闭按钮。当该窗口出现关闭按钮，就表示系统已经展开完毕。
<?php
if($admin){
?>
   //小白板的画线功能
   function undoOneStep()
   {
      var bd=document.getElementById("attachments");
      if(bd.childElementCount>0)
      {
         bd.removeChild(bd.children[bd.childElementCount-1]);
         saveBD(0);
      }
      //else newInfo("小白板已清空。");

   }

   document.addEventListener('DOMContentLoaded', function(){
      const canvas = document.getElementById('myLines');
      const ctx = canvas.getContext('2d',{ willReadFrequently:true });

      var nLeft=nTop=0;
      let isDrawing = false;

      function startDrawing (e) {
        isDrawing = true;
        nLeft=e.offsetX;		//奇怪的偏移量
        nTop=e.offsetY;
        draw(e);
      }
      function stopDrawing (e) {
        isDrawing = false;

        nLeft=e.offsetX<nLeft?e.offsetX:nLeft;
        nTop=e.offsetY<nTop?e.offsetY:nTop;

        ctx.beginPath() ;// Reset the path for the next draw

        var canvas_Original = document.createElement('CANVAS'),
        ctx_Original = canvas_Original.getContext('2d',{ willReadFrequently:true }),	//响应频繁读写开关
        img_Original = new Image;
        img_Original.crossOrigin = 'Anonymous';
        img_Original.onload = function()
        {

            canvas_Original.height = HEIGHT=img_Original.height;
            canvas_Original.width  = WIDTH=img_Original.width;
            ctx_Original.drawImage(img_Original,0,0);

            imgData=ctx_Original.getImageData(0,0,img_Original.width,img_Original.height);

            var arrData = new Uint32Array(imgData.data.buffer);		//通过Uint32Array的方式访问数据

            var m=n=0;
            var dx=dy=dw=dh=0;
            var bFound=false;
            for(m=0;m<HEIGHT;m++)						//在原图中匹配，修改遮罩图数据
            {
               for(n=0;n<WIDTH;n++)
               {
                  if(arrData[m*WIDTH+n]!=0x00000000)
                  {
                     bFound=true;
                     break;                   				//用Uint32处理，可以少三次赋值。
                  }
               }
               if(bFound) { break;}
            }
            bFound=false;
            dy=m;

            for(n=0;n<WIDTH;n++)
            {
               for(m=0;m<HEIGHT;m++)						//在原图中匹配，修改遮罩图数据
               {
                  if(arrData[m*WIDTH+n]!=0x00000000)
                  {
                     bFound=true;
                     break;                   				//用Uint32处理，可以少三次赋值。
                  }
               }
               if(bFound) { break;}
            }
            bFound=false;
            dx=n;

            for(m=HEIGHT-1;m>0;m--)						//在原图中匹配，修改遮罩图数据
            {
               for(n=0;n<WIDTH;n++)
               {
                  if(arrData[m*WIDTH+n]!=0x00000000)
                  {
                     bFound=true;
                     break;                   				//用Uint32处理，可以少三次赋值。
                  }
               }
               if(bFound) { break;}
            }
            bFound=false;
            dh=m;

            for(n=WIDTH-1;n>0;n--)
            {
               for(m=HEIGHT-1;m>0;m--)						//在原图中匹配，修改遮罩图数据
               {
                  if(arrData[m*WIDTH+n]!=0x00000000)
                  {
                     bFound=true;
                     break;                   				//用Uint32处理，可以少三次赋值。
                  }
               }
               if(bFound) { break;}
            }
            bFound=false;
            dw=n;

            var canvasCUT = document.createElement('CANVAS'),			//创建新的canvas对象
            ctxCUT = canvasCUT.getContext('2d',{ willReadFrequently:true }),	//响应频繁读写开关
            imgCUT = new Image;
            imgCUT.crossOrigin = 'Anonymous';
            canvasCUT.width    = dw-dx+1;
            canvasCUT.height   = dh-dy;					//设置尺寸大小
            imgData=ctx_Original.getImageData(dx,dy,dw-dx+1,dh-dy);		//获取非空矩形区域的图像数据
            ctxCUT.putImageData(imgData,0,0);					//将图像数据写入到新的ctx中。



            canvas_Original.height = HEIGHT=img_Original.height;
            canvas_Original.width  = WIDTH=img_Original.width;
            ctx_Original.drawImage(img_Original,0,0);

            $.post("./board/saveBDLINE.php?t=" + Math.random(), {'GID':gradeid,"IMGDATA":canvasCUT.toDataURL('image/png')}, function (data) 	//将canvas_Original的数据保存到服务器。
            {
               if(data.length>0){
                  var jData=JSON.parse(data);
                  canvas.height=canvas.width=0;

                  canvas.height=480;
                  canvas.width=650;
                  if(jData!=={})
                  {
                     var nPenWeight=(parseInt(document.getElementById("fontsize").value)-10)/4;

                     switch(nPenWeight)
                     {
                     case 6:

                        nLeft-=19;//减越多，越往左。
                        nTop+=21;
                     break;

                     case 5:
                        nLeft-=19;
                        nTop+=21;
                     break;

                     case 4:
                        nLeft-=18;
                        nTop+=22;
                     break;

                     case 3:
                        nLeft-=18;
                        nTop+=22;
                     break;

                     case 2:
                        nLeft-=17;
                        nTop+=23;
                     break;

                     case 1:
                        nLeft-=17;
                        nTop+=23;
                     break;
                     }

                     insertImg(jData[0],jData[1],nLeft,nTop,canvasCUT.width,canvasCUT.height);
                     saveBD(0);
                  }
               }		

            });
            canvas_Original = null;
         };
         img_Original.src = canvas.toDataURL("image/png");

 
      }



      function draw (e) {
 
         if (!isDrawing) return;

         nLeft=e.offsetX<nLeft?e.offsetX:nLeft;
         nTop=e.offsetY<nTop?e.offsetY:nTop;

         ctx.lineWidth = (parseInt(document.getElementById("fontsize").value)-10)/4;
         ctx.lineCap = 'round';
         ctx.strokeStyle = document.getElementById("color").value;//'#000';
         ctx.lineTo(e.offsetX - canvas.offsetLeft-26, e.offsetY - canvas.offsetTop+34);
         ctx.stroke();
         ctx.beginPath();
         ctx.moveTo(e.offsetX - canvas.offsetLeft-26, e.offsetY - canvas.offsetTop+34);
      }


      canvas.addEventListener('mousedown', startDrawing);
      canvas.addEventListener('mousemove', draw);
      canvas.addEventListener('mouseup', stopDrawing);
      //canvas.addEventListener('mouseout', stopDrawing);
   });
<?php
}
else{
?>
   document.getElementById("refreshWB").style.visibility="visible";				//小白板窗口刷新按钮。当该窗口出现关闭按钮，就表示系统已经展开完毕。
<?php
}
?>

</script>
<script>

//学生端更新小白板数据
function updateBOARD()
{
   //获取白板数据
   $.post("./board/getBDPos.php?t=" + Math.random(), {}, function (data) {
      if(data.length>0){ 

         var dataArr=data.split("\r\n");
         var strData=dataArr.slice(1,dataArr.length).join("");
         var posJSON=JSON.parse(strData);

         if(JSON.stringify(posJSON)=="{}") return;

         nSteps=posJSON.length;
         var bd=document.getElementById("attachments");
         for(var j=bd.children.length;j>0;j--)				//清理旧数据
         {
            bd.children[j-1].remove();
         }
         for(var i=0;i<posJSON.length;i++)				//生成新数据
         {

            //if(i>0)
            //   document.getElementById("back").style.visibility="visible";
            //else
            //   document.getElementById("back").style.visibility="hidden";

            //if(i<posJSON.length-1)
            //{
            //   document.getElementById("pause").style.visibility="visible";
            //}
            //else
            //{
            //   //clearInterval(inSteps);
            document.getElementById("pause").style.visibility="hidden";
            document.getElementById("all").style.visibility="hidden";
            document.getElementById("back").style.visibility="hidden";
            //}


            if(posJSON[i][0]=="DIV")
            {
               var divx = document.createElement("div");		//创建新DIV
               divx.style="position:absolute";
               divx.style.left=(parseInt(posJSON[i][2])+12)+"px";		//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               divx.style.top=(parseInt(posJSON[i][3])+16)+"px";
               divx.setAttribute("class","ATTACH");

               //var txt = document.createElement("div");		//创建新IMG：附件
               divx.innerHTML=posJSON[i][1];				//文本
               //divx.appendChild(txt);

               bd.appendChild(divx);
            }
            else  if(posJSON[i][0]=="IMG")
            {
               var divx = document.createElement("div");		//创建新DIV
               divx.style="position:absolute";
               divx.style.left=(parseInt(posJSON[i][2])+13)+"px";		//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               divx.style.top=(parseInt(posJSON[i][3])-5)+"px";
               divx.setAttribute("class","ATTACH");
               var img = document.createElement("img");			//创建新IMG：附件
               img.onmousedown=function(){drag(this,event,3);};
               img.src=posJSON[i][1];
               var imgName=posJSON[i][1].split("/");
               img.title=imgName[imgName.length-1];
               divx.appendChild(img);
               bd.appendChild(divx);					//往白板中插入此新DIV
            }
            else  if(posJSON[i][0]=="VIDEO")
            {
               var divx = document.createElement("div");		//创建新DIV
               divx.style="position:absolute";
               divx.style.left=(parseInt(posJSON[i][2])+12)+"px";		//教师端（有控制按钮）跟学生端不一样，所以有偏差。
               divx.style.top=(parseInt(posJSON[i][3])+16)+"px";
               divx.setAttribute("class","ATTACH");

               var video = document.createElement("video");			//创建新IMG：附件
               video.setAttribute("controls",true);
               video.setAttribute("height",posJSON[i][4]);
               video.onmousedown=function(){drag(this,event,3);};
               video.src=posJSON[i][1];
               //video.onmousedown=function(){dragBD(this,event)};		//添加后并不及时更新，而是等拖动后再更新。
               //video.title=nid;
               divx.appendChild(video);
               bd.appendChild(divx);					//往白板中插入此新DIV
            }
            else if(posJSON[i][0]=="COVER")
            {
               //var bd=document.getElementById("attachments");
               var d=document.createElement("DIV");
               d.style="width: 650px;    height: 480px;    position: absolute;    left: -4px;    top: 19px;    background: beige;border-radius: 3%;";
               d.setAttribute("class","ATTACH");
               //if(admin)
               //   d.ondblclick=function(){this.style.visibility="hidden";};
               d.onmousedown=function(){drag(this,event,1);};
               bd.appendChild(d);
            }
         }   
      }
      //else
      //{
      //   document.getElementById("back").style.visibility="hidden";
      //   document.getElementById("all").style.visibility="hidden";
      //   document.getElementById("pause").style.visibility="hidden";
      //}

      if(document.getElementById("WHITEBOARD").style.display!="block")
      {
         var o=document.getElementById("BDBTN");
         
o.innerHTML="<FONT COLOR=RED>i</font>";
      }
   });
}


function dealCommand(jsonCOMMAND)//接收到广播数据后，判断该执行什么操作。
{
   if(jsonCOMMAND.length>0)
   {
      var Sprite=null;
      switch(jsonCOMMAND[0])
      {

      //case "MSG":
      //      newInfo(jsonCOMMAND[1]+'||'+jsonCOMMAND[2],false);
      //   break;

      case "SETBGCOLOR":						//远程控制小白板背景色
         document.getElementById('WB').style.background=jsonCOMMAND[1];
         break;

      case "BDUPDATED":						//强制更新学生端小白板数据
         updateBOARD();
         break;

      }
   }
}

window.onload=function(){
   var o=document.getElementsByClassName("ace_text-input");
   o[0].disabled=true;
}

window.addEventListener('beforeunload', function(e) {
    // 现代浏览器
    if (e) {
        e.preventDefault();
        e.returnValue = '您确定要离开吗？未保存的更改可能会丢失。';
    }

    // 旧版浏览器
    return '您确定要离开吗？未保存的更改可能会丢失。';
});



/************************************************************
加载某个年级的小白板中已存素材
***********************************************************/
function loadBDAttach(taskID) {
   //if(admin)
   //{
      $.post("./board/loadBDAttach.php?t=" + Math.random(), { "GID": taskID }, function (data) {
         var o=document.getElementById("attach");
         o.length=0;
         if(data==""){
            o.add(new Option("暂无可用素材", ""));return;
         }
         data = eval(data);
         if (data.length > 0) {
            o.add(new Option("请选择图片/视频", ""));
            var proj=document.getElementById("newpp");
            if(proj){
               proj.value=data[0]==false?"未命名":data[0];
               proj.onclick=function(){createNew(proj.value);};
            }
            for (var i = 1; i < data.length; i++) {
               o.add(new Option(data[i], data[i]));
            }
         }
         else {
            o.add(new Option("暂无可用素材", ""));
         }
      });
   //}
}

</script>
</body>
</html>