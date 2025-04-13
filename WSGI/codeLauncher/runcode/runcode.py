import subprocess
import sys
import os


#处理C语言的代码
class RunCCode(object):
    
    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData
        self.compiler = "gcc"
        self.returnCode = -99;
        if not os.path.exists('running'):
            os.mkdir('running')                                     #创建保存代码文件的临时目录
    
    def _compile_c_code(self, filename, prog="./running/a.out"):    #编译代码
        cmd = [self.compiler, filename, "-Wall", "-o", prog]
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result

    def _run_c_prog(self, cmd="./running/a.out"):       #执行编译后的程序
        try:
            result = subprocess.run(cmd, input=self.stdinData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            #print(result.returncode)
            self.stdout=result.stdout
            self.stderr=result.stderr
            return result
        except subprocess.TimeoutExpired:
            #print("命令执行超时")
            #self.stdout="error"
            #self.stderr="error"
            #return "error"
            return -99
        
    def run_c_code(self, code=None):
        filename = "./running/test.c"                   #文件名
        if not code:
            code = self.code
        result_run = "No run done"
        with open(filename, "w") as f:
            f.write(code)                               #保存代码
        res = self._compile_c_code(filename)            #编译代码
        result_compilation = self.stdout + self.stderr  #编译结果
        if res == 0:
            self.returnCode=self._run_c_prog()                          #运行编译得到的程序
            result_run = self.stdout + self.stderr      #运行结果
        else:
            result_compilation="编译超时"
            result_run=""
            
        return self.returnCode,result_compilation, result_run           #返回运行结果


#处理C++语言的代码
class RunCppCode(object):

    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData
        self.compiler = "g++"
        self.returnCode = -99;
        if not os.path.exists('running'):
            os.mkdir('running')

    def _compile_cpp_code(self, filename, prog="./running/a.out"):  #编译代码
        cmd = [self.compiler, filename, "-Wall", "-o", prog]
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result

    def _run_cpp_prog(self, cmd="./running/a.out"):     #执行编译后的程序
        try:
            result = subprocess.run(cmd, input=self.stdinData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            #print(result.returncode)
            self.stdout=result.stdout
            self.stderr=result.stderr
            return result
        except subprocess.TimeoutExpired:
            #print("命令执行超时")
            #self.stdout="error"
            #self.stderr="error"
            return -99

    def run_cpp_code(self, code=None):
        filename = "./running/test.cpp"                 #文件名
        if not code:
            code = self.code
        result_run = "No run done"
        with open(filename, "w") as f:
            f.write(code)                               #保存代码
        res = self._compile_cpp_code(filename)          #编译代码
        result_compilation = self.stdout + self.stderr  #编译结果
        if res == 0:
            self.returnCode=self._run_cpp_prog()                        #运行编译得到的程序
            result_run = self.stdout + self.stderr      #运行结果
        else:
            result_compilation="编译超时"
            result_run=""
        return self.returnCode,result_compilation, result_run           #返回运行结果


#处理Python语言的代码
class RunPyCode(object):
    
    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData 
        if not os.path.exists('running'):
            os.mkdir('running')

    def _run_py_prog(self, cmd="a.py"):
        #cmd = [sys.executable, cmd]                                        #命令行下正常，WSGI下异常，会收到[mpm_winnt:crit]的错误信息：
                                                                            # AH02965: Child: UnaBle to retrieve my generation from the parent
        cmd = ["E:/WSGI/codeLauncher/menv/Scripts/python.exe", cmd]         #python必须是虚拟环境下的那个版本，否则会有权限问题

        try:
            result = subprocess.run(cmd, input=self.stdinData, capture_output=True,text=True,check=True,timeout=3)  # 设置超时时间为3秒
            #print(result.returncode)
            self.stdout=result.stdout
            self.stderr=result.stderr

        except subprocess.TimeoutExpired:               #命令执行超时
            self.stdout=""
            self.stderr="命令执行超时"

        except FileNotFoundError:
            self.stdout=""
            self.stderr="命令不存在"
        
        except subprocess.CalledProcessError as e:      #调用出错
            self.stdout= ""
            self.stderr= e.stderr+e.output

        except Exception as e:                          #异常情况
            self.stdout=""
            self.stderr= e.stderr+e.output

    def run_py_code(self, code=None):
        filename = "./running/a.py"                     #文件名
        if not code:
            code = self.code                            #提交的代码
        with open(filename, "w") as f:
            f.write(code)                               #保存代码
        self._run_py_prog(filename)                     #运行代码

        return self.stderr, self.stdout                 #返回运行结果
