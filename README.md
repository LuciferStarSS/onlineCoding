# onlineCoding
An online C/C++/Python coding platform.

本项目onlineCoding中绝大部分代码源自开源项目codeLauncher ( https://github.com/dadadel/codelauncher ) 。 

原项目只能在本地运行，运行后监听本地5000端口（ http://127.0.0.1:5000 ），
经过修改后，借助Apache2提供WEB服务，可以处理外部的访问请求。
将原项目的WEB页面功能全部剔除，只保留接收POST请求，以供WEB端用户调用。
在原项目的基础上，添加了对用户输入的支持。

具体部署，请查阅文档：onlineCoding在线编程平台部署.docx
