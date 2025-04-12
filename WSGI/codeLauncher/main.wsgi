import sys
import os

sys.path.insert(0,"E:/WSGI/codeLauncher")
os.chdir("E:/WSGI/codeLauncher")

activate_this='E:/WSGI/codeLauncher/menv/Scripts/activate_this.py'
with open(activate_this) as f:
    exec(f.read(),{'__file__':activate_this})

from webdev import app

application = app
