from flask import Flask, request
from flask_cors import CORS

from runcode import runcode                 #类RunCode定义：./runcode/runcode.py

app = Flask(__name__)

CORS(app)                                   #接口允许跨域访问

'''
本程序定义了三个只能接收POST数据的接口：
    /runc       用于处理提交的C代码
    /runcpp     用于处理提交的C++代码
    /runpy      用于处理提交的Python代码

处理过程包括源码的保存、编译和运行，最终返回运行结果/编译错误提示。

所有的处理，都在./runcode/runcode.py里。
'''
@app.route("/runc", methods=['POST'])       #处理C语言代码，只接受POST请求
def runc():
    postCode = request.form['CODE']         #表单数据：代码
    postData = request.form['DATA']         #表单数据：用户输入
    postArgs = request.form['ARGS']         #表单数据：运行参数
    postUN   = request.form['UN']           #$.post数据：用户名
    postCID  = request.form['CID']          #$.post数据：班级名称
    postPN   = request.form['PN']           #$.post数据：项目名称
    postDD   = request.form['DD']           #$.post数据：日期
        
    run = runcode.RunCCode(postCode, postData, postArgs,postUN,postCID,postPN,postDD) #创建处理C语言的对象
    resReturnCode,rescompil, resrun = run.run_c_code()      #运行C语言源代码
        
    if resrun:                                              #由于数据要以JSON的形式返回给JS，所以要对斜杠和双引号进行转义
        resrun =resrun.replace("\\","\\\\")
        resrun =resrun.replace("\"",'\\\"')
    if rescompil:
        rescompil =rescompil.replace("\\","\\\\")                        
        rescompil =rescompil.replace("\"",'\\\"')

    if resReturnCode == -99:                                #运行超时时，rescompil无数据，需要单独处理
        rescompil="运行超时"
            
    result="{ \"target\" : \"runc\", \"resrun\" : \""+resrun+"\", \"rescomp\" : \""+rescompil+"\" }"    #返回数据为JSON数据格式
    return result

@app.route("/runcpp", methods=['POST'])     #处理C++语言代码
def runcpp():
    postCode = request.form['CODE']
    postData = request.form['DATA']
    postArgs = request.form['ARGS']
    postUN   = request.form['UN']
    postCID  = request.form['CID']
    postPN   = request.form['PN']
    postDD   = request.form['DD']
        
    run = runcode.RunCppCode(postCode, postData, postArgs,postUN,postCID,postPN,postDD) #创建处理C++语言的对象
    resReturnCode,rescompil, resrun = run.run_cpp_code()
        
    if resrun:
        resrun =resrun.replace("\\","\\\\")
        resrun =resrun.replace("\"",'\\\"')
    if rescompil:
        rescompil =rescompil.replace("\\","\\\\")                        
        rescompil =rescompil.replace("\"",'\\\"')
            
    if resReturnCode == -99:
        rescompil="运行超时"
            
    result="{\"target\":\"runcpp\",\"resrun\":\""+resrun+"\",\"rescomp\":\""+rescompil+"\"}"
    return result

@app.route("/runpy", methods=['POST'])      #处理Python语言代码
def runpy():
    postCode = request.form['CODE']
    postData = request.form['DATA']
    postArgs = request.form['ARGS']
    postUN   = request.form['UN']
    postCID  = request.form['CID']
    postPN   = request.form['PN']
    postDD   = request.form['DD']
        
    run = runcode.RunPyCode(postCode, postData, postArgs,postUN,postCID,postPN,postDD) #创建处理Python语言的对象
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
