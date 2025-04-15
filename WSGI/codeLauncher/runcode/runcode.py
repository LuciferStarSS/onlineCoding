import subprocess, sys, os
from pathlib import Path

'''
RunCode类负责
    0.文件保存路径的创建
    1.文件数据保存
    2.编译C/C++代码
    3.执行C/C++编译后生成的程序，或直接执行Python代码
    4.返回执行后的输出信息
'''
#处理C语言的代码
class RunCCode(object):

    def __init__(self, postCode=None, postData=None, postArgs=None,postUN=None,postCID=None,postPN=None,postDD=None):
        self.postCode = postCode        #表单提交的代码数据
        self.postData = postData        #表单提交的用户输入数据
        self.postArgs = postArgs        #表单提交的命令行参数
        self.postUN   = postUN          #$.post提交的用户名
        self.postCID  = postCID         #$.post提交的班级名称
        self.postPN   = postPN          #$.post提交的项目名称
        self.postDD   = postDD          #$.post提交的日期
        
        self.compiler = "gcc"
        self.returnCode = -99;
        self.filePath = "./works/"+self.postCID+"/"+self.postDD     #文件保存路径：./works/班级名称/日期/
        
        if not os.path.exists(self.filePath):                       #创建保存代码文件的临时目录
            path=Path(self.filePath)
            path.mkdir(parents=True)
    
    def _compile_c_code(self, filename, prog="./running/a.out"):    #编译代码
        cmd = [self.compiler, filename, "-Wall", "-o", prog]
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result

    def _run_c_prog(self, cmd="./running/a.out"):                   #执行编译后的程序
        cmdArr = [cmd]
        if self.postArgs:                                           #程序执行时可以带参数，每个参数必须分隔开。
            for argData in self.postArgs.split(' '):
                cmdArr.append(argData)
        try:
            result = subprocess.run(cmdArr, input=self.postData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            self.stdout=result.stdout
            self.stderr=result.stderr
            return result
        except subprocess.TimeoutExpired:                           #运行超时
            return -99
        
    def run_c_code(self, code=None):
        filename = self.filePath+"/"+self.postUN+"_"+self.postPN    #代码的文件名（不带扩展名）
        if not code:
            code = self.postCode
        result_run = "出错：程序未运行"
        with open(filename+".c", "w") as f:
            f.write(code)                                           #保存代码
        returnCode = self._compile_c_code(filename+".c",filename+"_c.out") #编译代码，并输出到指定路径
        result_compilation = self.stdout + self.stderr              #编译结果
        if res == 0:                                                #编译成功
            returnCode=self._run_c_prog(filename+"_c.out")          #运行编译得到的程序
            result_run = self.stdout + self.stderr                  #运行结果
        #else:                                                      #编译出错，则直接输出
        #    result_compilation="编译异常"
        #    result_run=""
        return returnCode,result_compilation, result_run            #返回运行结果


#处理C++语言的代码
class RunCppCode(object):

    def __init__(self, postCode=None, postData=None, postArgs=None,postUN=None,postCID=None,postPN=None,postDD=None):
        self.postCode = postCode
        self.postData = postData
        self.postArgs = postArgs
        self.postUN   = postUN
        self.postCID  = postCID
        self.postPN   = postPN
        self.postDD   = postDD
        self.compiler = "g++"
        self.returnCode = -99;
        self.filePath = "./works/"+self.postCID+"/"+self.postDD

        if not os.path.exists(self.filePath):
            path=Path(self.filePath)
            path.mkdir(parents=True)
            
    def _compile_cpp_code(self, filename, prog="./running/a.out"):  #编译代码
        cmd = [self.compiler, filename, "-Wall", "-o", prog]
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result

    def _run_cpp_prog(self, cmd="./running/a.out"):                 #执行编译后的程序
        cmdArr = [cmd]
        if self.postArgs:
            for argData in self.postArgs.split(' '):
                cmdArr.append(argData)
        try:
            result = subprocess.run(cmdArr, input=self.postData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            self.stdout=result.stdout
            self.stderr=result.stderr
            return result
        except subprocess.TimeoutExpired:                           #运行超时
            return -99

    def run_cpp_code(self, code=None):
        filename = self.filePath+"/"+self.postUN+"_"+self.postPN    #文件名
        if not code:
            code = self.postCode
        result_run = "出错：程序未运行"
        with open(filename+".cpp", "w") as f:
            f.write(code)                                           #保存代码
        returnCode = self._compile_cpp_code(filename+".cpp", filename+"_cpp.out")  #编译代码
        result_compilation = self.stdout + self.stderr              #编译结果
        if returnCode == 0:                                         #编译成功
            returnCode=self._run_cpp_prog(filename+"_cpp.out")      #运行编译得到的程序
            result_run = self.stdout + self.stderr                  #运行结果
        return returnCode,result_compilation, result_run            #返回运行结果


#处理Python语言的代码
class RunPyCode(object):

    def __init__(self, postCode=None, postData=None, postArgs=None,postUN=None,postCID=None,postPN=None,postDD=None):
        self.postCode = postCode
        self.postData = postData
        self.postArgs = postArgs
        self.postUN   = postUN
        self.postCID  = postCID
        self.postPN   = postPN
        self.postDD   = postDD
        self.filePath = "./works/"+self.postCID+"/"+self.postDD
        
        if not os.path.exists(self.filePath):
            path=Path(self.filePath)
            path.mkdir(parents=True)

    def _run_py_prog(self, cmd="./running/a.py"):
        #cmdArr = [sys.executable, cmd]                 #命令行下正常，WSGI下异常，会收到[mpm_winnt:crit]的错误信息：
                                                        # AH02965: Child: UnaBle to retrieve my generation from the parent
                                                        #python必须是虚拟环境下的那个版本，否则会有权限问题。
        cmdArr = ["./menv/Scripts/python.exe", cmd ]    #修改main.wsgi，使用chdir后，这里就能用相对路径了。
        if self.postArgs:
            for argData in self.postArgs.split(' '):
                cmdArr.append(argData)
                
        try:
            result = subprocess.run(cmdArr, input=self.postData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            self.stdout=result.stdout
            self.stderr=result.stderr

        except subprocess.TimeoutExpired:                               #命令执行超时
            self.stdout=""
            self.stderr="命令执行超时"

        except FileNotFoundError:
            self.stdout=""
            self.stderr="命令不存在"
        
        except subprocess.CalledProcessError as e:                      #调用出错
            self.stdout= ""
            self.stderr= e.stderr+e.output

        except Exception as e:                                          #异常情况
            self.stdout=""
            self.stderr= e.stderr + e.output

    def run_py_code(self, code=None):
        filename = self.filePath+"/"+self.postUN+"_"+self.postPN+".py"      #文件名
        if not code:
            code = self.postCode                                        #提交的代码
        with open(filename, "w",encoding='utf-8') as f:
            f.write(code)                                               #保存代码
        self._run_py_prog(filename)                                     #运行代码

        return self.stderr, self.stdout                                 #返回运行结果
