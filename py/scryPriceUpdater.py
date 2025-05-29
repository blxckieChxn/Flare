import re
import urllib.request
import json
import mysql.connector
from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
import time
from unidecode import unidecode

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='flare',
    password='sdfsdf',
    database='flare'
)
cursor = conexion.cursor()

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
                
                if(data.split(" ")[0] == ""):
                    data = data.split("✶")[1]
                    
                # self.card_eur_price = data.split('')[0]
                self.card_eur_price=data
                self.in_card_price_eur_tag = False

# Price parser CURRENCY = USD
class CardPriceParserUsd(HTMLParser):
    def __init__(self):
        super().__init__()
        self.card_price = None
        self.in_card_price_tag = False

    def handle_starttag(self, tag, attrs):
        # Cambiar cuando la BD contenga datos sobre tipo FOIL
        # No te flipes tanto Mario del pasado (Margen de mejora)
        if tag == 'span':
            for attr in attrs:
                if attr[0] == 'class' and 'currency-usd' in attr[1]:
                    self.in_card_price_tag = True
                
    def handle_data(self, data):
        if self.in_card_price_tag:
            if not self.card_price:
               
                if(data.split(" ")[0] == ""):
                    data = data.split("✶")[1]
                    
                # Ahora si que obtenemos el precio de las cartas foil
                #self.card_price = data.split('✶')[1]
                self.card_price = data
                self.in_card_price_tag = False


def obtener_ediciones():
    cursor = conexion.cursor()
    cursor.execute('SELECT abreviatura FROM ediciones')

    resultado = cursor.fetchall()
    ediciones = [fila[0] for fila in resultado]
    return ediciones   

def obtener_nombre_carta(cursor, edicion):
    cursor = conexion.cursor()
    cursor.execute('SELECT c.nombre FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)' \
    ' where e.abreviatura= %s;', (edicion,))
    resultado=cursor.fetchall()
    nombres = [fila[0] for fila in resultado]
    return nombres

def obtener_numCarta(cursor, edicion, nombre):
    cursor.execute('SELECT numCarta FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)' \
    'WHERE e.abreviatura = %s AND c.nombre = %s;', (edicion, nombre,))
    resultado=cursor.fetchall()
    numeros = [fila[0] for fila in resultado]
    return numeros

def obtener_precio_carta_eur(url):
    try:
 
        # Abrir la URL y leer el contenido HTML
        response = urlopen(url)
        html = response.read().decode('utf-8')
        
        # Parsear el contenido HTML para obtener el precio de la carta, si dios quiere
        parserEur = CardPriceParserEur()
        parserEur.feed(html)

        # Devolver el precio de la carta si se encontró
        if (isinstance(parserEur.card_eur_price, str) and parserEur.card_eur_price != ""):
            return parserEur.card_eur_price
        else:
            print(f"No se encontró el precio de la carta en la direccion: \n {url}")
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
        if (isinstance(parserUsd.card_price, str) and parserUsd.card_price != ""):
            return parserUsd.card_price
        else:
            print(f"No se encontró el precio de la carta en la direccion: \n{url}.")
            return None
    except HTTPError as e:
        print("Error al obtener la página:", e)
        return False
    
def insertar_precio(precioEUR, precioUSD, cursor, nombre, numeroLimpio, edicion):
    cursor.execute('UPDATE cartas SET precio_eur = %s, precio_usd = %s WHERE nombre = %s AND numCarta = %s AND idEdicion = (SELECT idEdicion FROM ediciones WHERE abreviatura = %s);', (precioEUR, precioUSD, nombre, numeroLimpio, edicion,))
    conexion.commit()
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

def extraer_numero(texto):
    # Busca el primer número con decimal opcional
    coincidencias = re.findall(r'\d+(?:[\.,]\d+)?', texto)
    if coincidencias:
        return float(coincidencias[0].replace(",", "."))
    return 0.0

def main(cursor):
    # URL base de la API de Scryfall para buscar cartas por nombre
    url_base = "https://scryfall.com/card/"
    visitadas = set()
    url = ""

    # Construir la URL completa con los parámetros
    
    
    ediciones=obtener_ediciones() # Solo las que tu indiques
    for edicion in ediciones:
        nombres=obtener_nombre_carta(cursor, edicion)
        print("\n=============\n")
        print(edicion)
        print("\n=============\n")
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

                    # Obtener precio USD
                    precioUSD=obtener_precio_carta_usd(url)

                    if(precioUSD != False):
                        precioUSD = extraer_numero(precioUSD)
                        print(precioUSD)

                    # Obtener precio EUR
                    precioEUR=obtener_precio_carta_eur(url)

                    if(precioEUR != False):
                        precioEUR = extraer_numero(precioEUR)
                        if(precioEUR == 0.00):
                            precioEUR = precioUSD*1.13 # Conversion $ a €
                        print(precioEUR)

                    visitadas.add(url)

                    # Añadir los precios a la BD
                    insertar_precio(precioEUR, precioUSD, cursor, nombre, numeroLimpio, edicion)
                    time.sleep(0.5)

                
            
if __name__ == "__main__":
    main(cursor)          
                    




