import sys,os

appPath="d:/network/codeLauncher"
sys.path.insert(0,appPath)
os.chdir(appPath)            #这样处理后，runcode.py里就能用相对路径了。

from webdev import app
application = app
