from flask import Flask, request
from runcode import runcode
from flask_cors import CORS

app = Flask(__name__)

CORS(app)                                               #ajax跨域支持

@app.route("/runc", methods=['POST'])                   #处理C语言代码，只接受POST请求
def runc():
    #if request.method == 'POST':
        code = request.form['code']                     #code：表单中提交的C语言源代码
        stdinData = request.form['data']                #data：表单中提交的程序运行后需要输入的数据
        stdinArgs = request.form['args']                #data：表单中提交的程序运行后需要输入的数据
        run = runcode.RunCCode(code, stdinData, stdinArgs) #创建处理C语言的对象
        resReturnCode,rescompil, resrun = run.run_c_code() #运行C语言源代码
                                                        #后期考虑是否要添加运行时的命令行参数        
        
        if resrun:                                      #由于数据要以JSON的形式返回给JS，所以要对斜杠和双引号进行转义
            resrun =resrun.replace("\\","\\\\")
            resrun =resrun.replace("\"",'\\\"')
        if rescompil:
            rescompil =rescompil.replace("\\","\\\\")                        
            rescompil =rescompil.replace("\"",'\\\"')

        if resReturnCode == -99:
            rescompil="运行超时"
            
        #返回数据为JSON数据格式
        result="{ \"target\" : \"runc\", \"resrun\" : \""+resrun+"\", \"rescomp\" : \""+rescompil+"\" }"
        return result

@app.route("/runcpp", methods=['POST'])                #处理C++语言代码
def runcpp():
    #if request.method == 'POST':
        code = request.form['code']
        stdinData = request.form['data']
        stdinArgs = request.form['args']                #data：表单中提交的程序运行后需要输入的数据
        run = runcode.RunCppCode(code, stdinData, stdinArgs)
        resReturnCode,rescompil, resrun = run.run_cpp_code()
        
        if resrun:
            resrun =resrun.replace("\\","\\\\")
            resrun =resrun.replace("\"",'\\\"')
        if rescompil:
            rescompil =rescompil.replace("\\","\\\\")                        
            rescompil =rescompil.replace("\"",'\\\"')
            
        if resReturnCode == -99:
            rescompil="运行超时"
            
        #构建结果输出
        result="{\"target\":\"runcpp\",\"resrun\":\""+resrun+"\",\"rescomp\":\""+rescompil+"\"}"
        return result

@app.route("/runpy", methods=['POST'])                   #处理Python语言代码
def runpy():
    #if request.method == 'POST':
        code = request.form['code']
        stdinData = request.form['data']
        stdinArgs = request.form['args']                #data：表单中提交的程序运行后需要输入的数据
        run = runcode.RunPyCode(code, stdinData, stdinArgs)
        rescompil, resrun = run.run_py_code()

        if resrun:
            resrun =resrun.replace("\\","\\\\")
            resrun =resrun.replace("\"",'\\\"')
        if rescompil:
            rescompil =rescompil.replace("\\","\\\\")                        
            rescompil =rescompil.replace("\"",'\\\"')
            
        result="{\"target\":\"runpy\",\"resrun\":\""+resrun+"\",\"rescomp\":\""+rescompil+"\"}"
        return result

if __name__ == "__main__":
    app.run()
