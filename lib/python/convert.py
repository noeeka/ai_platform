
# -*- coding: utf-8 -*-
import urllib3
import io
from PIL import Image
import uuid
import sys
import os
import threading
import base64
import time


reload(sys)
sys.setdefaultencoding('utf-8')

http = urllib3.PoolManager()
file_object = open(str(os.path.abspath(os.path.join(os.getcwd(), ".."))) + '/tmp/content')
try:
    data = file_object.read()
finally:
    file_object.close()
r = http.request('POST', 'http://www.medtmt.net/XHGD/GateWay/Data/',
                 fields={'data': data, 'type': 'DATA'})
byte_stream = io.BytesIO(r.data)
roiImg = Image.open(byte_stream)
imgByteArr = io.BytesIO()
roiImg.save(imgByteArr, format='PNG')
imgByteArr = imgByteArr.getvalue()
imageName = str(uuid.uuid1())
with open(str(os.path.abspath(os.path.join(os.getcwd(), ".."))) + "/public/upload/infrared/" + imageName + ".png",
          "wb") as f:
    f.write(imgByteArr)
print(imageName + ".png")
