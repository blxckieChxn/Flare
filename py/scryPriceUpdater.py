import urllib.request
import json
import mysql.connector
from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
import time
from tqdm import tqdm
from unidecode import unidecode

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='mtg'
)
cursor = conexion.cursor()

# Obtener cantidad de entradas para actualizar la barra de tiempo
def obtener_filas(cursor):
    cursor.execute('SELECT COUNT(*) FROM cartas;')
    resultado = cursor.fetchall()
    cantFilas = str([fila[0] for fila in resultado])
    cantFilas = cantFilas.replace("[", "")  
    cantFilas = cantFilas.replace("]", "")
    cantFilas = int(cantFilas)
    print(f"{'Total actualizaciones: '}{cantFilas}")
    return cantFilas

# Price parser CURRENCY = EUR
class CardPriceParserEur(HTMLParser):
    def __init__(self):
        super().__init__()
        self.card_eur_price = None
        self.in_card_price_eur_tag = False

    def handle_starttag(self, tag, attrs):
        # Cambiar cuando la BD contenga datos sobre tipo FOIL
        if tag == 'span':
            for attr in attrs:
                if attr[0] == 'class' and 'currency-eur' in attr[1]:
                    self.in_card_price_eur_tag = True
                
    def handle_data(self, data):
        if self.in_card_price_eur_tag:
            if not self.card_eur_price: # Si no existe precio NO foil, obtendrá el precio de la carta foil eliminando el simbolo ✶
                data = data.split(" ")[0]
                self.card_eur_price = data.split('✶')[0]
                self.in_card_price_eur_tag = False

# Price parser CURRENCY = USD
class CardPriceParserUsd(HTMLParser):
    def __init__(self):
        super().__init__()
        self.card_price = None
        self.in_card_price_tag = False

    def handle_starttag(self, tag, attrs):
        # Cambiar cuando la BD contenga datos sobre tipo FOIL
        if tag == 'span':
            for attr in attrs:
                if attr[0] == 'class' and 'currency-usd' in attr[1]:
                    self.in_card_price_tag = True
                
    def handle_data(self, data):
        if self.in_card_price_tag:
            if not self.card_price:
                data = data.split(" ")[0]
                self.card_price = data.split('✶')[0]
                self.in_card_price_tag = False


def obtener_colecciones(cursor):
    
    cursor = conexion.cursor()
    cursor.execute('SELECT DISTINCT edicion FROM cartas;')
    # Todas las filas
    resultado = cursor.fetchall()
    colecciones = [fila[0] for fila in resultado]
    return colecciones
    

def obtener_nombre(cursor, edicion):
    cursor = conexion.cursor()
    cursor.execute('SELECT nombre FROM cartas where edicion = %s;', (edicion,))
    resultado=cursor.fetchall()
    nombres = [fila[0] for fila in resultado]
    return nombres

def obtener_numCarta(cursor, edicion, nombre):
    cursor.execute('SELECT num_carta FROM cartas WHERE edicion = %s AND nombre = %s;', (edicion, nombre,))
    resultado=cursor.fetchall()
    numeros = [fila[0] for fila in resultado]
    return resultado

def obtener_precio_carta_eur(url):
    try:
 
        # Abrir la URL y leer el contenido HTML
        response = urlopen(url)
        html = response.read().decode('utf-8')
        
        # Parsear el contenido HTML para obtener el precio de la carta, si dios quiere
        parserEur = CardPriceParserEur()
        parserEur.feed(html)

        # Devolver el precio de la carta si se encontró
        if parserEur.card_eur_price:
            return parserEur.card_eur_price
        else:
            print("No se encontró el precio de la carta en la página.")
            return None
    except HTTPError as e:
        print("Error al obtener la página:", e)
        return False

def obtener_precio_carta_usd(url):
    try:
 
        # Abrir la URL y leer el contenido HTML
        response = urlopen(url)
        html = response.read().decode('utf-8')

        parserUsd = CardPriceParserUsd()
        parserUsd.feed(html)

        # Devolver el nombre de la carta si se encontró
        if parserUsd.card_price:
            return parserUsd.card_price
        else:
            print("No se encontró el precio de la carta en la página.")
            return None
    except HTTPError as e:
        print("Error al obtener la página:", e)
        return False
    
def insertar_precio(precioEUR, precioUSD, cursor, nombre, numeroLimpio, edicion):
    cursor.execute('UPDATE cartas SET precio_eur = %s, precio_usd = %s WHERE nombre = %s AND num_carta = %s AND edicion = %s;', (precioEUR, precioUSD, nombre, numeroLimpio, edicion))
    cursor = conexion.commit()
    return None

def limpiarNombre(nombre):
    nombre = nombre.replace( "'", "")
    nombre = nombre.replace(" ", "-")
    nombre = nombre.lower()
    nombre = unidecode(nombre)
    return nombre

def check_visitada(visitadas, url):
    if url in visitadas:
        return True
    else:
        return False

def main(cursor):
    # URL base de la API de Scryfall para buscar cartas por nombre
    url_base = "https://scryfall.com/card/"
    visitadas = set()
    url = ""

    # Iniciar la barra de progreso
    total_iteraciones = obtener_filas(cursor)
    barra = tqdm(total=total_iteraciones, desc='Progreso')

    # Construir la URL completa con los parámetros
    # ediciones = obtener_colecciones(cursor) Todas las ediciones en la base de datos
    ediciones=['MKM', '2X2'] # Solo las que tu indiques
    for edicion in ediciones:
        nombres=obtener_nombre(cursor, edicion)
        print(edicion)
        for nombre in nombres:
            numeros=obtener_numCarta(cursor, edicion, nombre)
            for numero in numeros:
                # Limpiar el numero
                numeroLimpio = str(numero)
                numeroLimpio = numeroLimpio.replace(",","")
                numeroLimpio = numeroLimpio.replace("(","")
                numeroLimpio = numeroLimpio.replace(")","")
                numeroLimpio = int(numeroLimpio)

                # Edicion siempre en minusculas para la URL
                edicion = edicion.lower()
                
                # Limpiar caracteres raros del nombre
                nombreLimpio = limpiarNombre(nombre)
                url = f"{url_base}{edicion}{'/'}{numeroLimpio}{'/'}{nombreLimpio}"
                
                if check_visitada(visitadas, url) == False:
                    precioEUR=obtener_precio_carta_eur(url)
                    # Solucion: if(PrecioEUR):
                    if(precioEUR != "" and precioEUR != "\n" and precioEUR != None):
                        precioEUR=precioEUR.replace("€","")

                    if(precioEUR == "\n" or precioEUR == None):
                        precioEUR=float(0.00)
                    else:
                        precioEUR=float(precioEUR)

                    precioUSD=obtener_precio_carta_usd(url)
                    # Solucion: if(precioUSD):
                    if(precioUSD != "" and precioUSD != "\n" and precioUSD != None):
                        precioUSD=precioUSD.replace("$","")

                    if(precioUSD == "\n" or precioUSD == None):
                        precioUSD=float(0.00)
                    else:
                        precioUSD=float(precioUSD)

                    visitadas.add(url)

                    # Añadir los precios a la BD
                    insertar_precio(precioEUR, precioUSD, cursor, nombre, numeroLimpio, edicion)

                    # Actualizar barra
                    barra.update(1)
    barra.close()

                
                

if __name__ == "__main__":
    main(cursor)          
                    




