
#By Alberto Galera
import sys
import random
import json
import psutil
import hashlib
import time
import socket
from struct import *
from threading import Thread

import os

#################################### Functions network ####################################
def recvpackage(socket_cliente,size_package):
    package = socket_cliente.recv(int(size_package))
    if (len(package) != size_package):
        #fragment buffer
        Esperando = True
        while Esperando:
            if (len(package) != size_package):
                package = package + socket_cliente.recv(size_package - len(package))
                if (package == ""):
                    print "conexion broken"
                    break
            else:
                Esperando = False
    return package

def recvfile(socket_cliente, datos_cliente,url_file):
    #print "client ", datos_cliente," send file"
    f=open(str(url_file),"w")

    package = recvpackage(socket_cliente,8)
    size = unpack('d', package)
    size = size[0]
    temp = 0
    while temp < size:
        if (temp+ 1500 >= size):
            size_package = size - temp
        else:
            size_package = 1500

        package = recvpackage(socket_cliente,int(size_package))

        #print "quepachaaa"
        try:
            result = unpack(str(int(size_package))+'s', package)
            f.write(result[0])
        except:
            print "ERROR !!!!!!!!!!! size_package",str(int(size_package))
            print len(package)

        temp = temp+size_package
    f.close()
    string_md5 = recvpackage(socket_cliente,int(32))
    string_local_md5 = md5_for_file(url_file)
    if(string_md5 != string_local_md5):
        print "md5 fail!"
        print "local",string_local_md5, "len ", len(string_local_md5)
        print "external",string_md5, "len ", len(string_md5)
    #else: #mental else
    #	print "identic md5"

#################################### Function utils ####################################
def md5_for_file(url_file):
    md5 = hashlib.md5()
    with open(str(url_file),'rb') as f: 
        for chunk in iter(lambda: f.read(8192), b''): 
            md5.update(chunk)
    return md5.hexdigest()
def pick_speed(secs):

    value = psutil.network_io_counters(pernic=True)
    sub = value['eth0'][0]
    down = value['eth0'][1]
    time.sleep(secs)
    value = psutil.network_io_counters(pernic=True)

    actual_sub = int(((value['eth0'][0] - sub) * 0.0009765625 ) / secs)
    actual_down = int(((value['eth0'][1] - down) * 0.0009765625) / secs)
    return {
            'up': actual_sub,
            'down': actual_down
            }
#################################### Hard code ####################################

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((sys.argv[1], int(sys.argv[2])))
s.send(pack('i',1))
type_calc = 1
#tipo de render
s.send(pack('i',type_calc))
if (type_calc == 1):
	s.send(pack('i',1)) # pido archivo
	url = '/var/www/temp/'+str(random.randint(1, 999999999999))+'.json'
	recvfile(s,"datos",url)
	print url #return php
	s.send(pack('i',1)) # stop
s.close()

