import os
import mysql.connector
from PIL import Image
from tqdm import tqdm
import time

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='mtg'
)
cursor = conexion.cursor()

# Crear una tabla para almacenar las imágenes si no existe
cursor.execute('''CREATE TABLE IF NOT EXISTS cartas
                  (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255) NOT NULL, numCarta varchar(4) NOT NULL, edicion VARCHAR(4) NOT NULL, imagen LONGBLOB)''')

# Carpetas que contiene las imágenes
ruta = 'E:/magicCardScrap/'
carpetas = [
    "DBL",
    "HBG",
    "MIC",
    "MID",
    "NEC",
    "NEO",
    "SNC",
    "VOW"
]
total = 0
for carpeta in carpetas:
    lista = os.listdir(f"{ruta}{carpeta}")

    total = total + len(lista)

total_iteraciones = total
barra = tqdm(total=total_iteraciones, desc='Progreso')

for carpeta in carpetas:
    # Cambia de carpeta dependiendo de la edicion a la que pertenezcan las cartas -> carpeta = edicion
    carpeta_imagenes=f"{ruta}{carpeta}"

    # Recorrer la carpeta y agregar las imágenes a la base de datos
    for nombre_archivo in os.listdir(carpeta_imagenes):
        ruta_imagen = os.path.join(carpeta_imagenes, nombre_archivo)

        # Se divide el nombre despues de guardar la ruta, para obtener la info para insertar en la tabla
        nombre_archivo = nombre_archivo.split(".jpg")[0]
        numero_carta = nombre_archivo.split("_")[-1]
        nombre_archivo = nombre_archivo.split("_")[0]

        # 'rb' -> Read bytes
        with open(ruta_imagen, 'rb') as archivo: 
            imagen_bytes = archivo.read()
        cursor.execute('INSERT INTO cartas (nombre, numCarta, edicion, imagen) VALUES (%s, %s, %s, %s)', (nombre_archivo, numero_carta, carpeta, imagen_bytes))
        # Actualizar la barra de carga después de cada iteración
        barra.update(1)

barra.close()

# Confirmar los cambios y cerrar la conexión
conexion.commit()
conexion.close()