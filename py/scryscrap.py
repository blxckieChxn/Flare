import string
from urllib.request import urlopen, urlretrieve
from urllib.error import HTTPError
from html.parser import HTMLParser
import os
import re
from urllib.request import urlopen, HTTPError, Request
from html.parser import HTMLParser
from tqdm import tqdm
import argparse
from bs4 import BeautifulSoup

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

# Distingue el tag del nombre de la carta en el HTML de la página

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
        return 0
    
# Creo que se entiende
def obtener_info_carta(url):
    try:

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

# Limpia caracteres no permitidos
def limpiar_nombre_archivo(nombre_archivo):
    # Remover caracteres no permitidos en nombres de archivo
    nombre_archivo_limpio = re.sub(r'[<>:"/\\|?*]', '', nombre_archivo)
    # Eliminar todo después de '.jpg'
    nombre_archivo_limpio = nombre_archivo_limpio.split('.jpg')[0] + '.jpg'
    return nombre_archivo_limpio

# Creo que se entiende
def contar_cartas_edicion(url_edicion):
    
    try:
        # Abrir la URL
        with urlopen(url_edicion) as response:
            html = response.read()

        # Parsear el HTML
        soup = BeautifulSoup(html, 'html.parser')

        # Buscar el párrafo con la clase específica
        subline = soup.find('p', class_='set-header-title-subline')
        if not subline:
            print("No se encontró la etiqueta deseada.")
            return None

        texto = subline.get_text(strip=True)

        # Buscar número de cartas
        match = re.search(r'(\d+)\s+cards', texto)
        if match:
            return int(match.group(1))
        else:
            print("No se encontró el número de cartas.")
            return 0

    except Exception as e:
        print("Error:", e)
        return 0

# Generador de letra para cartas que se identifican con numero + letra
def sumarLetra():
    for letra in string.ascii_lowercase:  # 'a' a 'z'
        yield letra # return secuencial, no devuelve una lista sino que incrementa
                    # cada vez que es llamado

# Devuelve todas las ediciones que existen en el juego 
# (ultima comprobacion: 972 ediciones) 
# Busca todos los tags <small> </small> entonces pilla algunos que no corresponden a ediciones
# pero al consultar la url da fallo porque no existe y salta al siguiente

# ============= MEJORA ===========
# Las librerías como aiohttp y httpx (modo async) permiten hacer muchas peticiones en paralelo.
# [!] Pedir solo headers con HEAD en lugar de GET. (solo al buscar el nombre de las ediciones)


def getEditions(url):
    response = urlopen(url)
    html = response.read().decode('utf-8')
    small_tags = re.findall(r'<smal.*?>(.*?)</small>', html, re.DOTALL)
    editions = [tag.strip() for tag in small_tags]

    return editions

def main():
    
    # ================ MEJORA ==================
    # Se puede modularizar todo bastante más, pero meh, flojera
    # Hay mucho trabajo por hacer, si da tiempo al final puedo 
    # ponerme a modularizar, pero por ahora funciona y eso me vale


    # Obtener ediciones pasadas por parametros
    # Crear el parseador
    parser = argparse.ArgumentParser(description="Recibe varios strings por parámetro")
    
    # Definir la variable donde se van a almacenar
    # "+" Sirve para indicar que habrá 1 o n argumentos sin especificar el tamaño
    parser.add_argument("cadenas", nargs="+")

    # Definir vector donde se van a guardar los argumentos parseados
    args = parser.parse_args()

    # NOTA:
    # Si la edicion no está completa, como es el caso de los spoilers de futuras ediciones, 
    # la primera carta no tendrá el codigo nº1, asique no la recopilará. 
    # Como solución puedes cambiar la variable i 'contador de cartas de la 
    # edicion' 

    # Parametros de entrada
    # ABREVIACIONES SIEMPRE EN MINUSCULA
    ediciones = []
    totalCartas=0
    base_url = "https://scryfall.com/"

    # Lee las ediciones que entran por parametro
    for cad in args.cadenas:
        cad = cad.upper()

        # Si se indica 'all' descarga todas las ediciones 
        if(cad == "ALL" or cad == "all"):
            url = base_url + "sets/"
            print("[+] Obteniendo nombres de las ediciones...")
            ediciones = getEditions(url)
            break

        ediciones.append(cad)

    print("[+] Procesando ediciones...")
    
    # Barra progreso de la barra de progreso 
    # Sirve para obtener el tamaño que tendrá la descarga y poder poner la barra de progreso
    loadingEditionsBar = tqdm(total=len(ediciones), desc='Progreso')

    for edicion in ediciones:    
        urlEdicion = f"{base_url}{'sets/'}{edicion}"
        totalCartas+=contar_cartas_edicion(urlEdicion)
        loadingEditionsBar.update(1)

    # Cerrar barra ediciones
    loadingEditionsBar.close()
    # Barra progreso
    barra = tqdm(total=totalCartas, desc='Progreso')
    
    # Iteramos todas las ediciones de la lista y cada una se almacena en una carpeta
    for edicion in ediciones: 
            
            # Crear el directorio donde se almacenan las cartas de la edicion
            carpeta_imagenes = "/var/www/html/www/img/Scrap_Images/" + edicion
            os.makedirs(carpeta_imagenes, exist_ok=True)

            # Control de errores (Si conocemos el total de cartas sabemos donde acaba el bucle)
            cartasEdicion = contar_cartas_edicion(urlEdicion)
            print(f"Edicion: {edicion} -> Cartas: {cartasEdicion}\n")
            i=1 #contador de carta de la edicion

            imagen_url=""

            while (imagen_url != False):
                
                url = f"{base_url}{'card/'}{edicion}{'/'}{i}"
                imagen_url = obtener_imagen(url)
                
                
                # Cambiando el Return que hace la excepcion 404 en obtener_imagen()
                # por 0 conseguimos puentear la forma de salir del bucle while, 
                # de esta forma podemos controlar
                # los errores 404 cuando el indice sea menor que la cantidad de cartas de 
                # la edicion
                if(imagen_url == 0 and i < cartasEdicion):
                    
                    #====== APRENDIENDO A USAR GENERADORES =======
                    
                    # Inicializar generador
                    l = sumarLetra()
                    # Obtener siguiente letra
                    l = next(l)
                    # Sumar letra a URL
                    url += l
                    imagen_url = obtener_imagen(url)
                
                # Rompe el bucle
                elif(imagen_url == 0 and i == cartasEdicion):
                    imagen_url = False
                    break
                    
                # Esta linea no puede moverse arriba porque en el IF se actualiza la URL
                # en caso de carta con identificador con letras (Ex. 14a -> melding)
                nombreCarta = f"{obtener_info_carta(url)}{'_'}{i}{'.jpg'}"

                if imagen_url:
                    
                    # Actualizar barra de descarga
                    barra.update(1)
                    
                    try:                        
                        # Descargar la imagen y guardarla en la carpeta
                        urlretrieve(imagen_url, os.path.join(carpeta_imagenes, nombreCarta))
                        i+=1
                    
                    except Exception as e:
                          
                        print("Error al descargar la imagen:", e)
                        
                                
    barra.close()

if __name__ == "__main__":
    main()