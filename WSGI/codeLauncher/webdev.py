 from flask import Flask, render_template, request
from runcode import runcode
from flask_cors import CORS

app = Flask(__name__)

CORS(app)                                               #ajax跨域支持

default_c_code = """#include <stdio.h>

int main(int argc, char **argv)
{
    printf("Hello C World!!\\n");
    return 0;
}    
"""

default_cpp_code = """#include <iostream>

using namespace std;

int main(int argc, char **argv)
{
    cout << "Hello C++ World" << endl;
    return 0;
}
"""

default_py_code = """import sys
import os

if __name__ == "__main__":
    print ("Hello Python World!!")
"""

#default_rows = "15"
#default_cols = "60"

#@app.route("/")
@app.route("/runc", methods=['POST'])                   #只接受POST请求 #methods=['POST', 'GET'])
def runc():
    #if request.method == 'POST':
        code = request.form['code']
        stdinData = request.form['data']
        run = runcode.RunCCode(code,stdinData)
        rescompil, resrun = run.run_c_code()
        resrun =resrun.replace("\\","\\\\")
        resrun =resrun.replace("\"",'\\\"')
        
        rescompil =rescompil.replace("\\","\\\\")                        
        rescompil =rescompil.replace("\"",'\\\"')

        if not resrun:
            resrun = 'No result!'
            
        #构建结果输出
        result="{ \"target\" : \"runc\", \"resrun\" : \""+resrun+"\", \"rescomp\" : \""+rescompil+"\" }"
        return result

        
    #else:
    #    code = default_c_code
    #    resrun = 'No result!'
    #    rescompil = ''


    #    return render_template("main.html",
    #                       code=code,
    #                       target="runc",
    #                       resrun=resrun,
    #                       rescomp=rescompil,
    #                       rows=default_rows, cols=default_cols)

#@app.route("/cpp")
@app.route("/runcpp", methods=['POST'])#methods=['POST', 'GET'])
def runcpp():
    #if request.method == 'POST':
        code = request.form['code']
        stdinData = request.form['data']
        run = runcode.RunCppCode(code,stdinData)
        rescompil, resrun = run.run_cpp_code()
        resrun =resrun.replace("\\","\\\\")
        resrun =resrun.replace("\"",'\\\"')
        
        rescompil =rescompil.replace("\\","\\\\")                        
        rescompil =rescompil.replace("\"",'\\\"')


        if not resrun:
            resrun = 'No result!'
            
        #构建结果输出
        result="{\"target\":\"runcpp\",\"resrun\":\""+resrun+"\",\"rescomp\":\""+rescompil+"\"}"
        return result
    #else:
    #    code = default_cpp_code
    #    resrun = 'No result!'
    #    rescompil = ''

    #    return render_template("main.html",
    #                       code=code,
    #                       target="runcpp",
    #                       resrun=resrun,
    #                       rescomp=rescompil,
    #                       rows=default_rows, cols=default_cols)


#@app.route("/py")
@app.route("/runpy", methods=['POST'])#methods=['POST', 'GET'])
def runpy():
    #if request.method == 'POST':
        code = request.form['code']
        #stdinData = request.form['data']
        run = runcode.RunPyCode(code)
        rescompil, resrun = run.run_py_code()
        if not resrun:
            resrun = 'No result!'
        #构建结果输出
        resrun =resrun.replace("\\","\\\\")
        resrun =resrun.replace("\"",'\\\"')
        
        rescompil =rescompil.replace("\\","\\\\")                        
        rescompil =rescompil.replace("\"",'\\\"')
                                                 
        result="{\"target\":\"runpy\",\"resrun\":\""+resrun+"\",\"rescomp\":\""+rescompil+"\"}"
        return result
    #else:
    #    code = default_py_code
    #    resrun = 'No result!'
    #    rescompil = "No compilation for Python"

    #    return render_template("main.html",
    #                       code=code,
    #                       target="runpy",
    #                       resrun=resrun,
    #                       rescomp=rescompil,#"No compilation for Python",
    #                       rows=default_rows, cols=default_cols)


if __name__ == "__main__":
    app.run()
