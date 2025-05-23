#! /usr/bin/python
import glob
import time
import mysql.connector as mariadb
import json
import os

# Load DB credentials from JSON file in home directory
with open(os.path.expanduser('~/.d_hottub_db.json')) as f:
    db_cfg = json.load(f)

mariadb_connection = mariadb.connect(
    host=db_cfg.get('host', '127.0.0.1'),
    user=db_cfg['user'],
    password=db_cfg['password'],
    database=db_cfg['database']
)
cursor = mariadb_connection.cursor()
 
base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob(base_dir + '28*')[0]
device_file = device_folder + '/w1_slave'
 
def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines
 
def read_temp():
    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES':
        time.sleep(0.2)
        lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1:
        temp_string = lines[1][equals_pos+2:]
        temp_c = float(temp_string) / 1000.0
        temp_f = temp_c * 9.0 / 5.0 + 32.0
        return temp_c, temp_f
 
(temp_c,temp_f) = read_temp()
print("Tub Temp Is %s F" % temp_f)
sql_statement = "INSERT INTO tub (temperature) VALUES (%s)" % (temp_f)
cursor.execute(sql_statement)
mariadb_connection.commit()
