<?php
   $type=isset($_GET['t'])?$_GET['t']:"";
   $type=$type==""?"c":$type;

   $username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:header("Location: /class/");

   include "../include/config.inc.php";
   $room=1;

?><!DOCTYPE html>
<html lang=zh-cn>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=IE10">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="content-type" content="text/html;charset=UTF-8">
<title>在线编程系统</title>
<link rel="stylesheet" type="text/css" href="./static/devc.css">
<script>
   var username='<?php echo $username;?>';
   var classname='';
   var ext='';//<?php echo $type;?>';
   var qestionID=0;
</script>
<script src=./static/js/jquery.js></script>
</head>
<body>
<div align=center>
<div style="width: 100%;max-width: 1338px;margin-left:5px">
  <div align=center>
    <!--顶部-->
    <div alignstyle="0" style="border-radius: 3%;width: 100%;min-width:666px;height: 90px;">
      <div  align="center" style="font-size:20;width:100%;overflow:auto;"><h1>C/C++/Python编程平台</h1></div>
    </div>
    <div style="width: 100%;max-width: 1338px; margin-top:0px;margin-bottom:10px;min-width: 650px;">
      <div style="background:darkgray;height: 21px;">
        <div style="width:270px;height: 320px;position: relative;left:-50px">
           练习ID:<input type=text id=INPUT_TKID style="position: relative;left: 8px;" onclick="showTaskList()" placeholder="输入编号后敲回车也可打开">
           <div id=TASKLIST style="position: relative;  height: 200px; left:78px;visibility: hidden; z-index: 10; overflow: overlay; max-height: 170px; background: chocolate;"></div>
         </div>
      </div>
    </div>
    <!--顶部-->

    <!--左侧-->
    <div style="float:left;width: 666px;height:504px;margin-right: 6px;margin-bottom: 10px;background:beige ;/* margin-left: 10px; */">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <div style="text-align: left;"></div>
        <div style="text-align: left;">任务描述：</div>
        <textarea id=task></textarea>

        <div style="position: absolute;width: 300px;">
          <div style="text-align: left;">输入样例：</div>
          <textarea id="demoinput"></textarea>
        </div>
        <div   style="position: absolute;width: 300px;left: 331px;">
          <div style="text-align: left;">输出样例：</div>
          <textarea id="demooutput"></textarea>
        </div>
      </div>
    </div>
    <!--左侧-->

    <!--右侧-->
    <div alignstyle="0" style="float:left;width: 666px;height:504px;position:relative;background: beige;">
      <div alignstyle="0" style="width: 650px;height: 442px;left: 0px;top:6px;position: relative;">
        <div style="position: relative;text-align: left;height: 435px;background:yellow">
           <div style="left: 5px;position: relative;">内部评测数据列表</div>
           <select id="TTID" multiple="true" size="20" style="width: 640px;height: 200px; left:6px;position: relative;" onchange="getTest(this.value);"></select>
           <div style="position: absolute;width: 320px;left:6px;">
              <div style="text-align: left;">输入数据：</div>
                <textarea id="testinput"></textarea>
              </div>
              <div   style="position: absolute;width: 320px;left: 330px;">
                <div style="text-align: left;">输出数据：</div>
                <textarea id="testoutput"></textarea>
              </div>

           <div style="position: relative;top:166px">
                <input type=button id="launch-button" value="新建评测数据" onclick="newTest();" style="position: relative;width:210px;left:6px;height: 38px;font-size: 18px;font-weight: bolder;">
                <input type=button id="launch-button" value="保存评测数据" onclick="saveTest();" style="position: relative;width:210px;left:7px;height: 38px;font-size: 18px;font-weight: bolder;">
                <input type=button id="launch-button" value="删除评测数据" onclick="delTest();" style="position: relative;width:210px;left:8px;height: 38px;font-size: 18px;font-weight: bolder;">
           </div>
           </div>
           <div style="position: relative;top:8px;">
                <input type=button id="launch-button" value="新建练习" onclick="newTask();" style="position: relative;width:33%;left:2px;height: 44px;font-size: 20px;font-weight: bolder;float:left">
                <input type=button id="launch-button" value="保存练习" onclick="saveTask();" style="position: relative;width:33%;left:4px;height: 44px;font-size: 20px;font-weight: bolder;float:left">
                <input type=button id="launch-button" value="删除练习" onclick="delTask();" style="position: relative;width:33%;left:6px;height: 44px;font-size: 20px;font-weight: bolder;float:left">
           </div>

        </div>
      </div>
    </div>
    <!--右侧-->

    <!--底部-->
    <div style="float:left;width: 100%;max-width: 1338px; /* margin-top:10px; margin-right: 650px; */ margin-bottom:10px; min-width: 650px;">
       <div style="background:darkgray;text-align:center">Copyright</div>
    </div>
    <!--底部-->

  </div>
</div>
</div>

<script>

var taskID=0;
var testID="";
var lastTaskID=0;

//响应回车
document.addEventListener('keydown',function (event){
   if(event.keyCode == 13){
      if(document.getElementById("INPUT_TKID").value)
         getTask(document.getElementById("INPUT_TKID").value);
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


//界面初始化
function doTestCleaning()
{
   document.getElementById("testinput").value="";
   document.getElementById("testoutput").value="";
}

function doTaskCleaning()
{
   doTestCleaning();
   document.getElementById("task").value="";
   document.getElementById("demoinput").value="";
   document.getElementById("demooutput").value="";
}

//测试数据相关

//追加测试数据
function newTest()
{
   if(taskID)
   {
      $.post("./opt/editTest.php?act=new",{"TKID":taskID}, function(data){
         //alert(data);
         testID=data["TTID"];
         var oTID=document.getElementById("TTID");
         oTID.append(new Option(testID,testID));
         oTID.selectedIndex=oTID.length-1;
         doTestCleaning();
      },"json");
   }
   else alert("请先在任务列表中选择一项任务。");
}


//获取单个任务的配置信息
function getTest(value)
{
   if(value) 
   {
      testID=value;
      doTestCleaning();
      $.post("./opt/editTest.php?act=get", {"TKID":taskID,"TTID":value}, function (data) {
         var dataArr=data.split("<+-NOJSON-+>");
         if(dataArr.length==2)
         {
            document.getElementById("testinput").value=dataArr[0];
            document.getElementById("testoutput").value=dataArr[1];
         }
      });
   }
}


//保存测试数据
function saveTest()
{
   var inputData=  document.getElementById("testinput").value;
   var outputData= document.getElementById("testoutput").value;
   if(inputData && outputData && taskID && testID)
   {
      $.post("./opt/editTest.php?act=save",{"TKID":taskID,"TTID":testID,"IN":inputData,"OUT":outputData}, function(data){
         alert(data["SAVE"]);
      },"json");
   }
   else
      alert("请输入完整数据。");
}

//删除测试数据
function delTest()
{
   if(taskID && testID)
   {
      if(confirm("您确认要删除此评测数据么？\r\n此操作不可撤销。")==1)
      {
         $.post("./opt/editTest.php?act=del",{"TKID":taskID,"TTID":testID}, function(data){
            alert(data["DELETE"]);
            var oTID=document.getElementById("TTID");
            oTID.removeChild(oTID.children[oTID.selectedIndex]);
            doTestCleaning();
         },"json");
      }
   }
   else
      alert("请输入完整数据。");
}




//新建练习任务
function newTask()
{
   var oTKID=document.getElementById("TASKLIST");

   var lastTaskID=oTKID.children[oTKID.children.length-1];
   if(lastTaskID == undefined)
   {
      showTaskList();
      alert("当前未获取过练习列表，请重新再执行一次本操作。");
      //newTask();
   }
   else
   {
      $.post("./opt/editTask.php?act=new",{"TKID":(parseInt(lastTaskID.innerHTML)+1)}, function(data){
         if(data["TKID"])
         {
            taskID=data["TKID"];
            document.getElementById("INPUT_TKID").value=taskID;
            var oDiv=document.createElement("DIV");
            oDiv.innerText=taskID;
            oDiv.className="listcontent";
            oDiv.onclick=function(){
               getTask(this.innerText);
            }
            oTKID.append(oDiv);

            doTaskCleaning();
         }
      },"json");
   }
}

//保存任务
function saveTask()
{
   if(document.getElementById("TTID").length==0)
   {
      alert("内部评测数据为空，保存练习前，请先创建评测数据。");
   }
   else
   {
      saveTest();
      var taskDescription=document.getElementById("task").value;
      var inputData=  document.getElementById("demoinput").value;
      var outputData= document.getElementById("demooutput").value;

      if(inputData && outputData && taskID )
      {
         $.post("./opt/editTask.php?act=save",{"TKID":taskID,"DATA":taskDescription,"IN":inputData,"OUT":outputData}, function(data){
            alert(data["SAVE"]);
         },"json");
      }
      else
         alert("请输入完整数据。");
   }
}

//删除任务
function delTask()
{
   if(taskID)
   {
      if(confirm("您确认要删除此练习题目么？\r\n此操作不可撤销。")==1)
      {
         showTaskList();
         $.post("./opt/editTask.php?act=del",{"TKID":taskID}, function(data){
            alert(data["DELETE"]);
            document.getElementById("INPUT_TKID").value="";
            var oTID=document.getElementById("TKID");
            oTID.removeChild(oTID.children[oTID.selectedIndex]);
            doTaskCleaning();
            showTaskList();
         },"json");
      }
   }
}


//获取任务列表
function getTasks(value)
{
   $.get("./opt/editTask.php?act=all", {}, function (data) {
      //doTaskCleaning();
      //var o=document.getElementById("TKID");
      //o.length=0;
      //o.append(new Option("请选择要编辑的任务",""));
      //var arrKeys=Object.keys(data);
      //for(i=0;i<arrKeys.length;i++)
      //{
      //   o.append(new Option(data[arrKeys[i]],data[arrKeys[i]]));
      //}

            //doCleaning();
            var oTASKLIST=document.getElementById("TASKLIST");

            //var arrKeys=Object.keys(jsonData);
            for(i=0;i<data.length;i++)
            {
               var oDiv=document.createElement("DIV");
               oDiv.innerText=data[i];
               oDiv.className="listcontent";
               oDiv.onclick=function(){
                  getTask(this.innerText);
               }
               oTASKLIST.append(oDiv);
            }

      
   },"json");
}


//获取单个任务的配置信息
function getTask(value)
{
   if(value) 
   {
      doTaskCleaning();
      taskID=value;
      $.post("./opt/editTask.php?act=get", {"TKID":value,"E":1}, function (data) {
         var dataArr=data.split("<+-NOJSON-+>");

         //if(taskID)
         document.getElementById("INPUT_TKID").value=taskID;

         document.getElementById("TASKLIST").style.visibility="hidden";

         if(dataArr.length==4)
         {
            document.getElementById("task").value=dataArr[0];
            document.getElementById("demoinput").value=dataArr[1];
            document.getElementById("demooutput").value=dataArr[2];
            var testsArr=dataArr[3].split("|");
            document.getElementById("TTID").length=0;
            for(var i=0;i<testsArr.length;i++)
            {
               if(testsArr[i])
                  document.getElementById("TTID").append(new Option(testsArr[i],testsArr[i]));
            }
         }
      });
   }
}

//getTasks();//初始化任务列表

</script>
</body>
</html>