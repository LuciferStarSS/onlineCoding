import subprocess
import sys
import os


#处理C语言的代码
class RunCCode(object):
    
    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData
        self.compiler = "gcc"
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
        p = subprocess.Popen(cmd, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        
        if self.stdinData:
            p.stdin.write(self.stdinData.encode("utf-8"))
            p.stdin.flush()

        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result
    
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
            self._run_c_prog()                          #运行编译得到的程序
            result_run = self.stdout + self.stderr      #运行结果
        return result_compilation, result_run           #返回运行结果


#处理C++语言的代码
class RunCppCode(object):

    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData
        self.compiler = "g++"
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
        p = subprocess.Popen(cmd, stdin=subprocess.PIPE,  stdout=subprocess.PIPE, stderr=subprocess.PIPE)

        if self.stdinData:
            p.stdin.write(self.stdinData.encode("utf-8"))
            p.stdin.flush()

        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
        return result

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
            self._run_cpp_prog()                        #运行编译得到的程序
            result_run = self.stdout + self.stderr      #运行结果
        return result_compilation, result_run           #返回运行结果


#处理Python语言的代码
class RunPyCode(object):
    
    def __init__(self, code=None, stdinData=None):
        self.code = code
        self.stdinData = stdinData
        #self.stderr = ''
        #self.stdout = ''
        #self.stdinData = stdinData
        
        if not os.path.exists('running'):
            os.mkdir('running')

    def _run_py_prog(self, cmd="a.py"):
        #cmd = [sys.executable, cmd]
        cmd = ["E:/WSGI/codeLauncher/menv/Scripts/python.exe", cmd]         #python必须是虚拟环境下的那个版本，否则会有权限问题
        
        p = subprocess.Popen(cmd, stdin=subprocess.PIPE,  stdout=subprocess.PIPE, stderr=subprocess.PIPE)

        if self.stdinData:
            p.stdin.write(self.stdinData.encode("utf-8"))
            p.stdin.flush()
            
        result = p.wait()
        a, b = p.communicate()
        self.stdout, self.stderr = a.decode("utf-8"), b.decode("utf-8")
    
        return result
    
    def run_py_code(self, code=None):
        filename = "./running/aa.py"                     #文件名
        if not code:
            code = self.code                            #提交的代码
        with open(filename, "w") as f:
            f.write(code)                               #保存代码
        res = self._run_py_prog(filename)                     #运行代码
        return self.stderr,self.stdout  #self.stderr, self.stdout                 #返回运行结果
