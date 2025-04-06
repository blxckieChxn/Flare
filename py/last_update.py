import urllib.request
import json
import mysql.connector
from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
import time
from tqdm import tqdm

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='mtg'
)
cursor = conexion.cursor()

def obtener_update(cursor):
    cursor.execute('SELECT MAX(last_update) from cartas;')
    resultado = cursor.fetchone()
    resultado = str(resultado)
    resultado = resultado.strip('(')
    resultado = resultado.strip(')')
    resultado = resultado.strip(',')
    resultado = int(resultado)
    return resultado

def main(cursor):
    last = obtener_update(cursor)
    print(last)

    if(last == None):
        print("no last update")


if __name__ == "__main__":
    main(cursor)          
                    
