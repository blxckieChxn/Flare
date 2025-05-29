import os
import mysql.connector
from PIL import Image
from mysqlx import Error, IntegrityError
import requests
from tqdm import tqdm
import time

# VARIABLES
ruta = "/var/www/html/www/img/Scrap_Images/" # Carpetas que contiene las ediciones
base = "/var/www/html/www/"

carpetas = os.listdir(ruta) # Listar automaticamente las ediciones

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='flare',
    password='sdfsdf',
    database='flare'
)
cursor = conexion.cursor()
    
# Inserta la edicion con su abreviatura en la BD
# 
# Parametros -> String abreviatura
# Return ->  
def insertEdicion(abreviatura):

    nombre = getNombreEdicion(abreviatura)
    try:
        cursor.execute("INSERT INTO ediciones (idEdicion, nombre, abreviatura) VALUES (null, %s, %s)", (nombre, abreviatura))

        # Guardar los cambios
        conexion.commit()

    except IntegrityError as e:
        print("Error de integridad (clave duplicada, restricción):", e)

    except Error as e:
        print("Error general de MySQL:", e)

    except Exception as e:
        print("Otro error ocurrió:", e)

    finally:
        return True

# Obtiene el nombre completo de una edicion a partir de su abreviatura (CODE en Scryfall)
#
# Parametros -> String abreviatura
# Return -> String name
 
def getNombreEdicion(abreviatura):
    url = f'https://api.scryfall.com/sets/{abreviatura}'
    response = requests.get(url)

    if response.status_code == 200:
        data = response.json()
        return data.get('name')
    else:
        print(f"No se encontró la edición con abreviatura '{abreviatura}'.")
        return None
    
# Obtiene el total de cartas que hay en las carpetas de una ruta
# 
# Parametros -> List carpetas, String ruta
# Return -> Int total
def getTotalCartas(carpetas, ruta):
    # Contar el total de cartas en todas las carpetas para la barra de progreso
    total = 0
    for carpeta in carpetas:
        lista = os.listdir(f"{ruta}{carpeta}")

    total = total + len(lista)

    return total

# Devuelve el id de la edicion almacenado en la BD
# Parametros -> Cursor cursor, String abreviatura
# Return -> int id 
def getIdEdicion(abreviatura):

    cursor.execute("SELECT idEdicion from ediciones where abreviatura = %s", (abreviatura,))
    resultado = cursor.fetchone()

    if resultado:
        id = resultado[0]
    else:
        print('[!] No se encontró ninguna edición con esa abreviatura en la BD.')
        id=False
        
    return id

# Settear barra de progreso
total_iteraciones = getTotalCartas(carpetas, ruta)
barra = tqdm(total=total_iteraciones, desc='Progreso')


# ========== MEJORA ==========
# Modularizar
#=============================
# Insertar en BD
for carpeta in carpetas:
    # Si la edicion no esta en la BD se inserta, asi evitamos la primary key exception
    if(getIdEdicion(carpeta.upper()) == False):
        # Inserta la edicion en la BD
        insertEdicion(carpeta.upper())

        # Cambia de carpeta dependiendo de la edicion a la que pertenezcan las cartas -> carpeta = edicion
        carpeta_imagenes=f"{ruta}{carpeta}"

        # Recorrer la carpeta y agregar las imágenes a la base de datos
        for nombre_archivo in os.listdir(carpeta_imagenes):
            ruta_imagen = os.path.relpath(ruta, base)
            ruta_imagen = ruta_imagen + "/" + carpeta + "/"
            ruta_imagen = os.path.join(ruta_imagen, nombre_archivo)
            
            # Se divide el nombre despues de guardar la ruta, para obtener la info 
            # para insertar en la tabla
            nombre_archivo = nombre_archivo.split(".jpg")[0]
            numero_carta = nombre_archivo.split("_")[-1]
            nombre_archivo = nombre_archivo.split("_")[0]

            # El nombre de la carpeta es la abreviatura de la edicion
            idEdicion = getIdEdicion(carpeta)
            
            cursor.execute('INSERT INTO cartas (idCarta, nombre, numCarta, precio_eur, precio_usd, imagen, idEdicion) VALUES (null, %s, %s, null, null, %s, %s)', (nombre_archivo, numero_carta, ruta_imagen, idEdicion,))
            # Actualizar la barra de carga después de cada iteración
            barra.update(1)
        
    print(f"La edición: {carpeta.upper()} ya existe en BD")

barra.close()

# Confirmar los cambios y cerrar la conexión
conexion.commit()
conexion.close()