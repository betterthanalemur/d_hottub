#! /usr/bin/python3
import board
import digitalio
import busio
import time
import adafruit_bme280
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

# Create library object using our Bus I2C port
i2c = busio.I2C(board.SCL, board.SDA)
bme280 = adafruit_bme280.Adafruit_BME280_I2C(i2c, address=0x76)

# OR create library object using our Bus SPI port
#spi = busio.SPI(board.SCK, board.MOSI, board.MISO)
#bme_cs = digitalio.DigitalInOut(board.D10)
#bme280 = adafruit_bme280.Adafruit_BME280_SPI(spi, bme_cs)

# change this to match the location's pressure (hPa) at sea level
bme280.sea_level_pressure = 1013.25
temp = bme280.temperature * 9 / 5 + 32
humid = bme280.humidity
pressure = bme280.pressure
alt = bme280.altitude

print("\nTemperature: %0.1f F" % temp)
print("Humidity: %0.1f %%" % humid)
print("Pressure: %0.1f hPa" % pressure)
print("Altitude = %0.2f meters" % alt)
sql_statement = "INSERT INTO ambient (temperature,pressure,humidity,altitude) VALUES (%s,%s,%s,%s)" % (temp, pressure, humid, alt)
cursor.execute(sql_statement)
mariadb_connection.commit()
