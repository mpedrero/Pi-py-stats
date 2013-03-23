#By Alberto Galera
import codecs
import json
import psutil
import hashlib
import time
import socket
from struct import pack, unpack
from threading import Thread

#import os
import os.path
#################################### Globals ####################################

url_file_location = "/tmp/pi-py-stats.json"
generate_time = 10*60 # seconds
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

def sendfile(socket_cliente, datos_cliente,url_file):
    f = open(str(url_file), 'rb')

    size = os.path.getsize(str(url_file))
    print size
    temp = 0
    print "client ", datos_cliente," recv render"
    
    #le mando el tamanio del archivo
    socket_cliente.send(pack('d',size))

    while temp < size:
        if (temp+ 1496 >= size):
            size_package = size - temp
            #print "entra"
        else:
            size_package = 1496
            #print "no entra"

        data = f.read(size_package)#[temp:(temp+size_package)]
        package = pack(str(size_package)+'s',data)
        socket_cliente.send(package)

        temp = temp+size_package
    f.close()
    socket_cliente.send(pack('32s',md5_for_file(url_file)))
#################################### Function utils ####################################
def md5_for_file(url_file):
    md5 = hashlib.md5()
    with open(str(url_file),'rb') as f: 
        for chunk in iter(lambda: f.read(8192), b''): 
            md5.update(chunk)
    return md5.hexdigest()
def pick_speed_in_secs(secs):

    value = psutil.network_io_counters(pernic=True)
    sub = value['eth0'][0]
    down = value['eth0'][1]
    time.sleep(secs)
    value = psutil.network_io_counters(pernic=True)

    actual_sub = int(((value['eth0'][0] - sub) * 0.0009765625 ) / secs)
    actual_down = int(((value['eth0'][1] - down) * 0.0009765625) / secs)
    return [actual_sub,actual_down]
def pick_speed_avg(value):
    sub = value['eth0'][0]
    down = value['eth0'][1]
    value = psutil.network_io_counters(pernic=True)

    avg_sub = int(((value['eth0'][0] - sub) * 0.0009765625 ) / generate_time)
    avg_down = int(((value['eth0'][1] - down) * 0.0009765625) / generate_time)
    return [avg_sub, avg_down, value]
#################################### Hard code ####################################
class Stats(Thread):
    def __init__(self):
        Thread.__init__(self)
        #self.socket = socket_cliente
        #self.datos = datos_cliente
    
    def run(self):
        speed_avg = psutil.network_io_counters(pernic=True)
        seguir = True
        while seguir:
            #calculate time generate stats
            start = time.time()
            #calculate stats
            # speed network #
            network_actual = pick_speed_in_secs(2)
            network_avg = pick_speed_avg(speed_avg);
            speed_avg = network_avg[2];
            # pack all #
            data = {
                "network_down": network_actual[1],#
                "network_up": network_actual[0],#
                "network_avg_down": network_avg[1],#
                "network_avg_up": network_avg[0],#
                "cache": psutil.cached_phymem(),#
                "buffer": psutil.phymem_buffers(),#
                #"ava": psutil.avail_phymem(),#
                "used": psutil.used_phymem(),#
                "swap_total": psutil.total_virtmem(),#
                #"swap_ava": psutil.avail_virtmem(),#
                "swap_used": psutil.used_virtmem(),#
                "hdd_use_": psutil.disk_usage('/')[3],
                "hdd_use_home": psutil.disk_usage('/home')[3],
                "cpu_use": psutil.cpu_percent(interval=1),#
                "cpu_mhz": int(os.popen("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq").read()[:-3]),
                "temp": int(int(os.popen("cat /sys/class/thermal/thermal_zone0/temp").read())/1000)
            }
            data_string = json.dumps(data)
            #print 'ENCODED:', data_string
            temp = time.localtime(start)
            datatime = str(temp[2])+"/"+str(temp[1])+"/"+str(temp[0])+" "+str(temp[3])+"/"+str(temp[4])+"/"+str(temp[5])
                
            if (os.path.exists(url_file_location) == True):
                f=codecs.open(str(url_file_location),"a", "utf-8")
                f.write(', '+'"'+datatime+'":'+data_string)
            else:
                f=open(str(url_file_location),"a")
                f.write(u'"'+datatime+'":'+data_string)
            f.close()
            
            del data
            del data_string
            del temp
            del datatime
            del network_actual

            time.sleep(generate_time - int(time.time() - start))

class Cliente(Thread):
    def __init__(self, socket_cliente, datos_cliente):
        Thread.__init__(self)
        self.socket = socket_cliente
        self.datos = datos_cliente

    def run(self):
        package = recvpackage(self.socket,4)
        print "package",package
        if (package != ""):
            result = unpack('i', package)
            if(result[0] == 1):
                sendfile(self.socket,self.datos,str(url_file_location))
            elif(result[0] == 0):
                self.socket.send(pack('i',0))
                self.socket.close()
                #print "client ", self.datos," complete"

#################################### Init code ####################################
if __name__ == '__main__':
    generate_stats = Stats()
    generate_stats.start()

    # Se prepara el servidor
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.bind(("", 8002))
    server.listen(5)
    print "Wait clients..."
    while 1:
        # Se espera a un cliente
        socket_cliente, datos_cliente = server.accept()
        
        # Se escribe su informacion
        print "conectado "+str(datos_cliente)
        print datos_cliente
        
        # Se crea la clase con el hilo y se arranca.
        package = recvpackage(socket_cliente,4)
        package = unpack('i', package)
        if(package[0] == 1):
            hilo3 = Cliente(socket_cliente, datos_cliente)
            hilo3.start()
        #elif(package[0] == 0):
        #   servers.append([socket_cliente,datos_cliente,'file_return',0,'hilo','frame'])
        #   print "server anadido a la lista"
        elif(package[0] == 9):
            print "test mode"
            #recvfile(socket_cliente,datos_cliente,'/tmp/test.tar.gz')