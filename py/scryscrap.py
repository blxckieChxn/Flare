from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
import os
import re
from urllib.request import urlopen, HTTPError, Request
from html.parser import HTMLParser
from tqdm import tqdm
import time


# Distingue el tag de imagen en el HTML de la página
class ImageParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.img_urls = []

    def handle_starttag(self, tag, attrs):
        if tag == 'img':
            for attr, value in attrs:
                if attr == 'src':
                    self.img_urls.append(value)

class CardNameParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.card_name = None
        self.in_card_name_tag = False

    def handle_starttag(self, tag, attrs):
        if tag == 'span':
            for attr in attrs:
                if attr[0] == 'class' and 'card-name' in attr[1]:
                    self.in_card_name_tag = True

    def handle_data(self, data):
        if self.in_card_name_tag:
            self.card_name = data.strip()
            self.in_card_name_tag = False

def obtener_imagen(url):
    try:
        # Abrir la URL y leer el contenido HTML
        response = urlopen(url)
        html = response.read().decode('utf-8')
        
        # Parsear el contenido HTML con html.parser
        parser = ImageParser()
        parser.feed(html)
        
        if parser.img_urls:
            # Obtener la URL de la primera imagen encontrada
            img_url = parser.img_urls[0]
            return img_url
        else:
            print("No se encontró ninguna imagen en la página.")
            return None
    except HTTPError as e:
        print("Error al obtener la página:", e)
        return False
    
def obtener_info_carta(url):
    try:

        # Precio carta almacenado en <meta name="twitter:data2" content="$11.05" /> 
        # Abrir la URL y leer el contenido HTML
        response = urlopen(url)
        html = response.read().decode('utf-8')
        
        # Parsear el contenido HTML para obtener el nombre de la carta
        parser = CardNameParser()
        parser.feed(html)
        
        # Devolver el nombre de la carta si se encontró
        if parser.card_name:
            return parser.card_name
        else:
            print("No se encontró el nombre de la carta en la página.")
            return None
    except HTTPError as e:
        print("Error al obtener la página:", e)
        return False

def limpiar_nombre_archivo(nombre_archivo):
    # Remover caracteres no permitidos en nombres de archivo
    nombre_archivo_limpio = re.sub(r'[<>:"/\\|?*]', '', nombre_archivo)
    # Eliminar todo después de '.jpg'
    nombre_archivo_limpio = nombre_archivo_limpio.split('.jpg')[0] + '.jpg'
    return nombre_archivo_limpio


def main():
    
    

    base_url = "https://scryfall.com/card/"

    # Si la edicion no está completa, como es el caso de los spoilers de futuras ediciones, la primera carta no tendrá
    # el codigo nº1, asique no la recopilará. Como solución puedes cambiar la variable i 'contador de cartas de la 
    # edicion' 
    # ABREVIACIONES SIEMPRE EN MINUSCULA
    ediciones = [
        "hbg",
        "snc",
        "neo",
        "nec",
        "dbl",
        "vow",
        "mid",
        "mic",
        "j21"
    ]

    # Barra progreso (538 es un estimador aproximado de la media de cartas por edicion)
    total_iteraciones = 538 * len(ediciones)
    barra = tqdm(total=total_iteraciones, desc='Progreso')

    

    for edicion in ediciones: #iteramos todas las ediciones de la lista y cada una se almacena en una carpeta
            carpeta_imagenes = edicion.upper()
            os.makedirs(carpeta_imagenes, exist_ok=True)
            i=1 #contador de carta de la edicion
            imagen_url=""

            while (imagen_url != False):
                
                url = f"{base_url}{edicion}{'/'}{i}"
                imagen_url = obtener_imagen(url)
                nombreCarta = f"{obtener_info_carta(url)}{'_'}{i}{'.jpg'}"
                # Actualizar barra de descarga
                barra.update(1)

                if imagen_url:
                    try:                        
                        # Descargar la imagen y guardarla en la carpeta
                        urlretrieve(imagen_url, os.path.join(carpeta_imagenes, nombreCarta))
                        i+=1
                        
                    except Exception as e:
                        print("Error al descargar la imagen:", e)
                        i+=1          
    barra.close()

if __name__ == "__main__":
    main()